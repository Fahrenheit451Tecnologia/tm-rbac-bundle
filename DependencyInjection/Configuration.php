<?php declare(strict_types=1);

namespace TM\RbacBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use TM\RbacBundle\Model\Permission;
use TM\RbacBundle\Model\Role;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tm_rbac');

        $rootNode
            ->fixXmlConfig('permission')
            ->children()
                ->arrayNode('manager')
                    ->children()
                        ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('models')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('permission')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('role')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('user')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('listeners')
                    ->children()
                        ->booleanNode('permission')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('permissions')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}