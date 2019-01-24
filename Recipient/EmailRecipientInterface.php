<?php

namespace Yokai\MessengerBundle\Recipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
interface EmailRecipientInterface
{
    /**
     * @return string
     */
    public function getEmail();
}
