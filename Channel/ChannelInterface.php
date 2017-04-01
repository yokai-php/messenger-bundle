<?php

namespace Yokai\MessengerBundle\Channel;

use Yokai\MessengerBundle\Delivery;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
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
