<?php

namespace Yokai\MessengerBundle\Channel\Swiftmailer\Configurator;

use Swift_Message;
use Yokai\MessengerBundle\Delivery;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
interface SwiftMessageConfiguratorInterface
{
    /**
     * @param Swift_Message $message
     * @param Delivery      $delivery
     */
    public function configure(Swift_Message $message, Delivery $delivery);
}
