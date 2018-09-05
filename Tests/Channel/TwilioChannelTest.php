<?php

namespace Yokai\MessengerBundle\Tests\Channel;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Channel\Twilio\Factory\ClientFactoryInterface;
use Yokai\MessengerBundle\Channel\TwilioChannel;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Tests\Channel\Mock\Twilio\Factory\MockClientFactory;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\DoctrineRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\MobileRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\SwiftmailerRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\TwilioRecipient;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
class TwilioChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    protected function setUp()
    {
        $this->clientFactory = new MockClientFactory();
    }

    protected function tearDown()
    {
        unset($this->clientFactory);
    }

    /**
     * @param  array $defaults
     * @return TwilioChannel
     */
    protected function createChannel(array $defaults)
    {
        return new TwilioChannel($this->clientFactory, $defaults);
    }

    /**
     * @dataProvider supportsRecipientProvider
     */
    public function testSupportsRecipient($recipient, $supports)
    {
        $channel = $this->createChannel([]);
        $this->assertSame($supports, $channel->supports($recipient));
    }

    public function testConfigure()
    {
        $resolver = new OptionsResolver();

        $channel = $this->createChannel([
            'option_that_do_not_exists' => 'unknown',
            'from' => '+330601020304',
            'api_id' => 'azertyuiop',
            'api_token' => 'qsdfghjklm',
        ]);
        $channel->configure($resolver);

        $this->assertSame(
            [
                'from' => '+330601020304',
                'api_id' => 'azertyuiop',
                'api_token' => 'qsdfghjklm',
            ],
            $resolver->resolve([])
        );
        $this->assertSame(
            [
                'from' => '+330605040302',
                'api_id' => 'wxcvbnwxcvbn',
                'api_token' => 'azerty',
            ],
            $resolver->resolve(
                [
                    'from' => '+330605040302',
                    'api_id' => 'wxcvbnwxcvbn',
                    'api_token' => 'azerty',
                ]
            )
        );
    }
    /**
     * @dataProvider isSendingSmsProvider
     */
    public function testIsSendingSms($from, $to, $exceptionExpected)
    {
        $delivery = new Delivery(
            'test',
            $to,
            [
                'from' => $from,
                'api_id' => 'azertyuiop',
                'api_token' => 'qsdfghjklm',
            ],
            '',
            'body',
            [
            ],
            [
            ]
        );

        $channel = $this->createChannel([]);

        $exceptionRaised = false;
        try {
            $channel->handle($delivery);
        } catch (\Exception $exception) {
            $exceptionRaised = true;
        }

        self::assertSame($exceptionRaised, $exceptionExpected);
    }

    /**
     * @return array
     */
    public function isSendingSmsProvider()
    {
        return [
            [
                '+15005550001',
                '+15005551234',
                true,
            ],
            [
                '+15005550008',
                '+15005551234',
                true,
            ],
            [
                '+15005550007',
                '+15005551234',
                true,
            ],
            [
                '+15005550006',
                '+15005550001',
                true,
            ],
            [
                '+15005550006',
                '+15005550002',
                true,
            ],
            [
                '+15005550006',
                '+15005550003',
                true,
            ],
            [
                '+15005550006',
                '+15005550004',
                true,
            ],
            [
                '+15005550006',
                '+15005550009',
                true,
            ],
            [
                '+15005550006',
                '+15005551234',
                false,
            ],
            [
                '+15005550006',
                new TwilioRecipient('+15005551234'),
                false,
            ],
        ];
    }

    public function supportsRecipientProvider()
    {
        return [
            [
                new DoctrineRecipient('1'),
                false,
            ],
            [
                new SwiftmailerRecipient('john.doe@acme.org'),
                false,
            ],
            [
                new TwilioRecipient('+330601020304'),
                true,
            ],
            [
                '+330601020304',
                true,
            ],
            [
                new MobileRecipient(['foo', 'bar']),
                false,
            ],
        ];
    }
}
