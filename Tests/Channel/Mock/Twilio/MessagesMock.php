<?php

namespace Yokai\MessengerBundle\Tests\Channel\Mock\Twilio;

use Twilio\Exceptions\RestException;
use Twilio\Rest\Api;
use Twilio\Rest\Api\V2010;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client as TwilioClient;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
class MessagesMock
{
    /**
     * @var TwilioClient
     */
    private $client;

    /**
     * @param TwilioClient $client
     */
    public function __construct(TwilioClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $to
     * @param array $data
     *
     * @return MessageInstance
     *
     * @throws RestException
     * @throws \Exception
     */
    public function create($to, array $data)
    {
        if (!isset($data['from']) || !isset($data['body'])) {
            throw new \LogicException('Body or from must be defined');
        }

        switch ($data['from']) {
            case '+15005550001':
                throw new RestException('This phone number is invalid.', 21212, 400);
            case '+15005550008':
                throw new RestException('This number has an SMS message queue that is full.', 21611, 400);
            case '+15005550006':
                break;
            case '+15005550007':
            default:
                throw new RestException(
                    'This phone number is not owned by your account or is not SMS-capable.',
                    21606,
                    400
                );
        }

        switch ($to) {
            case '+15005550001':
                throw new RestException('This phone number is invalid.', 21211, 400);
            case '+15005550002':
                throw new RestException('Twilio cannot route to this number.', 21612, 400);
            case '+15005550003':
                throw new RestException(
                    'Your account doesn\'t have the international permissions necessary to SMS this number.',
                    21408,
                    400
                );
            case '+15005550004':
                throw new RestException('This number is blacklisted for your account.', 21610, 400);
            case '+15005550009':
                throw new RestException('This number is incapable of receiving SMS messages.', 21614, 400);
        }

        $uri = '/2010-04-01/Accounts/ACa98602a33da95e8d2d126765094a179a/Messages/';
        $uri .= 'SM291f8ad74ede445e9d50e1d7898ce9b6.json';
        $media = '/2010-04-01/Accounts/ACa98602a33da95e8d2d126765094a179a/Messages/';
        $media .= 'SM291f8ad74ede445e9d50e1d7898ce9b6/Media.json';
        $payload = [
            'sid' => 'SM291f8ad74ede445e9d50e1d7898ce9b6',
            'date_created' => 'Wed, 18 Apr 2018 11:38:00 +0000',
            'date_updated' => 'Wed, 18 Apr 2018 11:38:00 +0000',
            'date_sent' => null,
            'account_sid' => 'ACa98602a33da95e8d2d126765094a179a',
            'to' => '+33671566640',
            'from' => '+15005550006',
            'messaging_service_sid' => null,
            'body' => $data['body'],
            'status' => 'queued',
            'num_segments' => '1',
            'num_media' => '0',
            'direction' => 'outbound-api',
            'api_version' => '2010-04-01',
            'price' => null,
            'price_unit' => 'USD',
            'error_code' => null,
            'error_message' => null,
            'uri' => $uri,
            'subresource_uris' => [
                'media' => $media,
            ],
        ];
        $domain = new Api($this->client);
        $version = new V2010($domain);

        return new MessageInstance($version, $payload, 'ACa98602a33da95e8d2d126765094a179a');
    }
}
