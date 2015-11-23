<?php

namespace MessengerBundle\Channel;

use MessengerBundle\Delivery;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
interface ChannelInterface
{
    /**
     * @param mixed $recipient
     *
     * @return bool
     */
    public function supports($recipient);

    /**
     * @param OptionsResolver $resolver
     */
    public function configure(OptionsResolver $resolver);

    /**
     * @param Delivery $delivery
     */
    public function handle(Delivery $delivery);
}
