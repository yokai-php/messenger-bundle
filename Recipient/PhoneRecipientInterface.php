<?php

namespace Yokai\MessengerBundle\Recipient;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
interface PhoneRecipientInterface
{
    /**
     * @return string
     */
    public function getPhone();
}
