<?php

namespace Yokai\MessengerBundle\Tests\Channel;

use Yokai\MessengerBundle\Channel\SwiftmailerChannel;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\DoctrineRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\SwiftmailerRecipient;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class SwiftmailerChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $mailer;

    protected function setUp()
    {
        $this->mailer = $this->prophesize(\Swift_Mailer::class);
    }

    protected function tearDown()
    {
        unset(
            $this->mailer
        );
    }

    protected function createChannel(array $defaults)
    {
        return new SwiftmailerChannel(
            $this->mailer->reveal(),
            $defaults
        );
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
            'from' => 'no-reply@test.test',
        ]);
        $channel->configure($resolver);

        $this->assertSame(['from' => 'no-reply@test.test'], $resolver->resolve([]));
        $this->assertSame(['from' => 'please-reply@test.test'], $resolver->resolve(['from' => 'please-reply@test.test']));
    }

    public function testIsSendingEmail()
    {
        $recipient = new SwiftmailerRecipient('john.doe@test.test');

        $messageProphecy = Argument::allOf(
            Argument::type(\Swift_Message::class),
            Argument::which('getSubject', 'subject'),
            Argument::which('getBody', 'body'),
            Argument::which('getFrom', ['no-reply@test.test' => null]),
            Argument::which('getTo', ['john.doe@test.test' => null])
        );

        $this->mailer->send($messageProphecy)
            ->shouldBeCalledTimes(2);

        $channel = $this->createChannel([]);

        $channel->handle(
            new Delivery(
                'test',
                $recipient,
                [
                    'from' => 'no-reply@test.test'
                ],
                'subject',
                'body',
                [
                ]
            )
        );

        $channel->handle(
            new Delivery(
                'test',
                'john.doe@test.test',
                [
                    'from' => 'no-reply@test.test'
                ],
                'subject',
                'body',
                [
                ]
            )
        );
    }

    public function supportsRecipientProvider()
    {
        return [
            [
                new DoctrineRecipient(1),
                false,
            ],
            [
                new SwiftmailerRecipient('john.doe@acme.org'),
                true,
            ],
            [
                'not an email',
                false,
            ],
            [
                'john.doe@acme.org',
                true,
            ],
        ];
    }
}
