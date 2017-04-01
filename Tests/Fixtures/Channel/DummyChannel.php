<?php

namespace Yokai\MessengerBundle\Tests\Fixtures\Channel;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Channel\ChannelInterface;
use Yokai\MessengerBundle\Delivery;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class DummyChannel implements ChannelInterface
{
    public function supports($recipient)
    {
    }

    public function configure(OptionsResolver $resolver)
    {
    }

    public function handle(Delivery $delivery)
    {
    }
}
