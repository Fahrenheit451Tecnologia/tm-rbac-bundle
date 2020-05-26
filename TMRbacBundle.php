<?php

namespace TM\RbacBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TMRbacBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver([
                realpath(__DIR__.'/Resources/config/doctrine/model')    => 'TM\RbacBundle\Model',
            ], [
                'tm_rbac.manager.name'
            ]))
        ;
    }
}
