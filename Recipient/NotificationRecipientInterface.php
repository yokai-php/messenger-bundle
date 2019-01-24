<?php

namespace Yokai\MessengerBundle\Recipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
interface NotificationRecipientInterface
{
    /**
     * @return array
     */
    public function getDevicesTokens();
}
