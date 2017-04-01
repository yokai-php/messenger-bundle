<?php

namespace Yokai\MessengerBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Yokai\MessengerBundle\DependencyInjection\CompilerPass\ConfigureSenderCompilerPass;
use Yokai\MessengerBundle\DependencyInjection\CompilerPass\RegisterMobileAdapterCompilerPass;
use Yokai\MessengerBundle\DependencyInjection\CompilerPass\RegisterSwiftmailerConfiguratorCompilerPass;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class YokaiMessengerBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $mappingDir = __DIR__.'/Resources/config/model';
        $namespace = __NAMESPACE__.'\Entity';

        $loadEntitiesCompilerPass = DoctrineOrmMappingsPass::createXmlMappingDriver(
            [$mappingDir => $namespace],
            [],
            'yokai_messenger.load_doctrine_orm_mapping',
            [$this->getName() => $namespace]
        );

        $container
            ->addCompilerPass(new ConfigureSenderCompilerPass())
            ->addCompilerPass(new RegisterSwiftmailerConfiguratorCompilerPass())
            ->addCompilerPass(new RegisterMobileAdapterCompilerPass())
            ->addCompilerPass($loadEntitiesCompilerPass)
        ;
    }
}
