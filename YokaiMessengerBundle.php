<?php

namespace Yokai\MessengerBundle;

use Yokai\MessengerBundle\DependencyInjection\CompilerPass\ConfigureSenderCompilerPass;
use Yokai\MessengerBundle\DependencyInjection\YokaiMessengerExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class YokaiMessengerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new YokaiMessengerExtension('yokai_messenger');
    }

    /**
     * {@inheritdoc}
     */
    public function registerCommands(Application $application)
    {
        // commands are registered using dependency injection tags
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ConfigureSenderCompilerPass())
        ;
    }
}
