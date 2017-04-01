<?php

namespace Yokai\MessengerBundle\Recipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
interface SwiftmailerRecipientInterface
{
    /**
     * @return string
     */
    public function getEmail();
}
