<?php

namespace Yokai\MessengerBundle\DependencyInjection\CompilerPass;

use Sly\NotificationPusher\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yokai\DependencyInjection\CompilerPass\ArgumentRegisterTaggedServicesCompilerPass;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class RegisterMobileAdapterCompilerPass extends ArgumentRegisterTaggedServicesCompilerPass
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'yokai_messenger.mobile_channel',
            'yokai_messenger.mobile_adapter',
            AdapterInterface::class,
            1
        );
    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        // If swiftmailer is not enabled just return (avoid exceptions)
        if (!$container->hasParameter('yokai_messenger.mobile_enabled') ||
            !$container->getParameter('yokai_messenger.mobile_enabled')
        ) {
            return;
        }

        parent::process($container);
    }
}
