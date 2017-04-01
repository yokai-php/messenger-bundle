<?php

namespace Yokai\MessengerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yokai\DependencyInjection\CompilerPass\ArgumentRegisterTaggedServicesCompilerPass;
use Yokai\MessengerBundle\Channel\Swiftmailer\Configurator\SwiftMessageConfiguratorInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class RegisterSwiftmailerConfiguratorCompilerPass extends ArgumentRegisterTaggedServicesCompilerPass
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'yokai_messenger.swiftmailer_chain_message_configurator',
            'yokai_messenger.swiftmailer_message_configurator',
            SwiftMessageConfiguratorInterface::class,
            0
        );
    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        // If swiftmailer is not enabled just return (avoid exceptions)
        if (!$container->hasParameter('yokai_messenger.swiftmailer_enabled') ||
            !$container->getParameter('yokai_messenger.swiftmailer_enabled')
        ) {
            return;
        }

        parent::process($container);
    }
}
