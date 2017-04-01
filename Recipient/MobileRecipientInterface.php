<?php

namespace Yokai\MessengerBundle\Recipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
interface MobileRecipientInterface
{
    /**
     * @return array
     */
    public function getDevicesTokens();
}
