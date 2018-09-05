<?php

namespace Yokai\MessengerBundle\Recipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
interface DoctrineRecipientInterface
{
    /**
     * @return string
     */
    public function getId();
}
