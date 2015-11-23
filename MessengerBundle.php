<?php

namespace MessengerBundle;

use MessengerBundle\DependencyInjection\CompilerPass\ConfigureSenderCompilerPass;
use MessengerBundle\DependencyInjection\MessengerExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class MessengerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new MessengerExtension('messenger');
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
