<?php

namespace Yokai\MessengerBundle;

use Yokai\MessengerBundle\DependencyInjection\CompilerPass\ConfigureSenderCompilerPass;
use Yokai\MessengerBundle\DependencyInjection\CompilerPass\RegisterMobileAdapterCompilerPass;
use Yokai\MessengerBundle\DependencyInjection\CompilerPass\RegisterSwiftmailerConfiguratorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class YokaiMessengerBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ConfigureSenderCompilerPass())
            ->addCompilerPass(new RegisterSwiftmailerConfiguratorCompilerPass())
            ->addCompilerPass(new RegisterMobileAdapterCompilerPass())
        ;
    }
}
