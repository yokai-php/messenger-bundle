<?php

namespace Yokai\MessengerBundle\Channel\Twilio\Factory;

use Twilio\Rest\Client as TwilioClient;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
interface ClientFactoryInterface
{
    /**
     * @param string $twilioId
     * @param string $twilioToken
     *
     * @return TwilioClient
     */
    public function createClient($twilioId, $twilioToken);
}
