<?php

namespace MessengerBundle\Tests\Fixtures\Channel;

use MessengerBundle\Channel\ChannelInterface;
use MessengerBundle\Delivery;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
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
