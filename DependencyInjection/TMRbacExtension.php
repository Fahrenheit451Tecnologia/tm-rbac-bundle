<?php declare(strict_types=1);

namespace TM\RbacBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TMRbacExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        foreach ([
            'permission',
            'role',
            'user'
        ] as $model) {
            $container->setParameter(sprintf('tm_rbac.model.%s.class', $model), $config['models'][$model]);
        }

        foreach ($config['permissions'] as $key => $value) {
            if (!preg_match('/^[a-z_]+$/', $key)) {
                throw new \Exception(sprintf(
                    'Invalid permission key "%s". Permissions keys must contain only lower case characters or underscores',
                    $key
                ));
            }
        }

        $container->setParameter('tm_rbac.permissions', $config['permissions']);

        if ($config['listeners']['permission']) {
            $definition = $container->getDefinition('tm_rbac.event_listener.permission');

            $definition
                ->addTag('kernel.event_listener', [
                    'event' => KernelEvents::CONTROLLER,
                ])
            ;
        }
    }
}