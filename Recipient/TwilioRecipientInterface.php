<?php

namespace Yokai\MessengerBundle\Recipient;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
interface TwilioRecipientInterface
{
    /**
     * @return string
     */
    public function getPhone();
}
