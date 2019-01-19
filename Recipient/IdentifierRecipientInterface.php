<?php

namespace Yokai\MessengerBundle\Recipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
interface IdentifierRecipientInterface
{
    /**
     * @return string
     */
    public function getId();
}
