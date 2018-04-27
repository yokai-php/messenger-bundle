<?php

namespace Yokai\MessengerBundle\Tests\Channel\Mock\Twilio;

use Twilio\Http\Client as HttpClient;
use Twilio\Rest\Client as TwilioClient;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
class MockTwilioClient extends TwilioClient
{
    /**
     * @var MessagesMock
     */
    public $messages;

    /**
     * @inheritdoc
     */
    public function __construct(
        $username = null,
        $password = null,
        $accountSid = null,
        $region = null,
        HttpClient $httpClient = null,
        array $environment = null
    ) {
        parent::__construct($username, $password, $accountSid, $region, $httpClient, $environment);

        $this->messages = new MessagesMock($this);
    }
}
