<?php

namespace Yokai\MessengerBundle\Channel\Swiftmailer\Configurator;

use Swift_Message;
use Yokai\MessengerBundle\Delivery;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class ChainMessageConfigurator implements SwiftMessageConfiguratorInterface
{
    /**
     * @var SwiftMessageConfiguratorInterface[]
     */
    private $configurators;

    /**
     * @param SwiftMessageConfiguratorInterface[] $configurators
     */
    public function __construct($configurators)
    {
        $this->configurators = $configurators ?: [];
    }

    /**
     * @inheritdoc
     */
    public function configure(Swift_Message $message, Delivery $delivery)
    {
        foreach ($this->configurators as $configurator) {
            $configurator->configure($message, $delivery);
        }
    }
}
