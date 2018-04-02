<?php declare(strict_types=1);

namespace TM\RbacBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\KernelEvents;
use TM\RbacBundle\EventListener\PermissionListener;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class TMRbacExtension extends Extension
{
    const LIMIT_KEY_LENGTH  = 255;
    const LIMIT_NAME_LENGTH = 255;

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

        foreach ($config['permissions'] as $key => $name) {
            if (!preg_match('/^[a-z_]+$/', $key)) {
                throw new \Exception(sprintf(
                    'Invalid permission key "%s". Permissions keys must contain only lower case characters or underscores',
                    $key
                ));
            }

            if (strlen($key) > self::LIMIT_KEY_LENGTH) {
                throw new \Exception(sprintf(
                    'Invalid permission key "%s". Permission key must be under %d characters, this is %d characters',
                    $key,
                    self::LIMIT_KEY_LENGTH,
                    strlen($key)
                ));
            }

            if (strlen($name) > self::LIMIT_NAME_LENGTH) {
                throw new \Exception(sprintf(
                    'Invalid permission name "%s" for key "%s". Permission name must be under %d characters, this is %d characters',
                    $name,
                    $key,
                    self::LIMIT_NAME_LENGTH,
                    strlen($name)
                ));
            }
        }

        $container->setParameter('tm_rbac.permissions', $config['permissions']);

        if ($config['listeners']['permission']) {
            $definition = $container->getDefinition(PermissionListener::class);

            $definition
                ->addTag('kernel.event_listener', [
                    'event' => KernelEvents::CONTROLLER,
                ])
            ;
        }
    }
}