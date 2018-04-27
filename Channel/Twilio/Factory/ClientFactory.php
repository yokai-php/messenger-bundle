<?php

namespace Yokai\MessengerBundle\Channel\Twilio\Factory;

use Twilio\Rest\Client as TwilioClient;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
class ClientFactory implements ClientFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createClient($twilioId, $twilioToken)
    {
        return new TwilioClient($twilioId, $twilioToken);
    }
}
