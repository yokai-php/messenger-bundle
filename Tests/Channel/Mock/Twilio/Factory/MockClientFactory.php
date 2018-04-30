<?php

namespace Yokai\MessengerBundle\Tests\Channel\Mock\Twilio\Factory;

use Yokai\MessengerBundle\Channel\Twilio\Factory\ClientFactoryInterface;
use Yokai\MessengerBundle\Tests\Channel\Mock\Twilio\MockTwilioClient;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
class MockClientFactory implements ClientFactoryInterface
{
    /**
     * @param string $twilioId
     * @param string $twilioToken
     *
     * @return MockTwilioClient
     */
    public function createClient($twilioId, $twilioToken)
    {
        return new MockTwilioClient($twilioId, $twilioToken);
    }
}
