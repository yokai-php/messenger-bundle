<?php

namespace Yokai\MessengerBundle\Recipient;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
interface MobileRecipientInterface
{
    /**
     * @return array
     */
    public function getDevicesTokens();
}
