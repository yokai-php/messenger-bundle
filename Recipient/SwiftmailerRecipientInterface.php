<?php

namespace MessengerBundle\Recipient;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
interface SwiftmailerRecipientInterface
{
    /**
     * @return string
     */
    public function getEmail();
}
