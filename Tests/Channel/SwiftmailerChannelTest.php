<?php

namespace Yokai\MessengerBundle\Tests\Channel;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Channel\Swiftmailer\Configurator\SwiftMessageConfiguratorInterface;
use Yokai\MessengerBundle\Channel\SwiftmailerChannel;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\DoctrineRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\MobileRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\SwiftmailerRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\TwilioRecipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class SwiftmailerChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $mailer;

    /**
     * @var ObjectProphecy
     */
    private $configurator;

    protected function setUp()
    {
        $this->mailer = $this->prophesize(\Swift_Mailer::class);
        $this->configurator = $this->prophesize(SwiftMessageConfiguratorInterface::class);
    }

    protected function tearDown()
    {
        unset(
            $this->mailer,
            $this->configurator
        );
    }

    /**
     * @param  array $defaults
     * @return SwiftmailerChannel
     */
    protected function createChannel(array $defaults)
    {
        return new SwiftmailerChannel(
            $this->mailer->reveal(),
            $this->configurator->reveal(),
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
        $delivery1 = new Delivery(
            'test',
            new SwiftmailerRecipient('john.doe@test.test'),
            [
                'from' => 'no-reply@test.test'
            ],
            'subject',
            'body',
            [
            ],
            [
            ]
        );

        $delivery2 = new Delivery(
            'test',
            'john.doe@test.test',
            [
                'from' => 'no-reply@test.test'
            ],
            'subject',
            'body',
            [
            ],
            [
            ]
        );

        $this->configurator->configure(Argument::type(\Swift_Message::class), $delivery1)
            ->shouldBeCalled()
            ->will(function ($args) {
                /** @var \Swift_Message $message */
                $message = $args[0];
                $message->setSubject('subject');
                $message->setBody('body');
                $message->setFrom('no-reply@test.test');
                $message->setTo('john.doe@test.test');
            });

        $this->configurator->configure(Argument::type(\Swift_Message::class), $delivery2)
            ->shouldBeCalled()
            ->will(function ($args) {
                /** @var \Swift_Message $message */
                $message = $args[0];
                $message->setSubject('subject');
                $message->setBody('body');
                $message->setFrom('no-reply@test.test');
                $message->setTo('john.doe@test.test');
            });

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

        $channel->handle($delivery1);
        $channel->handle($delivery2);
    }

    public function testArrayFrom()
    {
        $delivery1 = new Delivery(
            'test',
            new SwiftmailerRecipient('john.doe@test.test'),
            [
                'from' => [
                    'no-reply@test.test' => 'NoReply'
                ]
            ],
            'subject',
            'body',
            [
            ],
            [
            ]
        );

        $delivery2 = new Delivery(
            'test',
            'john.doe@test.test',
            [
                'from' => [
                    'no-reply@test.test'  => 'NoReply',
                    'no-reply2@test.test' => 'NoReply2'
                ]
            ],
            'subject',
            'body',
            [
            ],
            [
            ]
        );

        $this->configurator->configure(Argument::type(\Swift_Message::class), $delivery1)
            ->shouldBeCalled()
            ->will(function ($args) {
                /** @var \Swift_Message $message */
                $message = $args[0];
                $message->setSubject('subject');
                $message->setBody('body');
                $message->setFrom(['no-reply@test.test' => 'NoReply']);
                $message->setTo('john.doe@test.test');
            });

        $this->configurator->configure(Argument::type(\Swift_Message::class), $delivery2)
            ->shouldBeCalled()
            ->will(function ($args) {
                /** @var \Swift_Message $message */
                $message = $args[0];
                $message->setSubject('subject');
                $message->setBody('body');
                $message->setFrom([
                    'no-reply@test.test'  => 'NoReply',
                    'no-reply2@test.test' => 'NoReply2'
                ]);
                $message->setTo('john.doe@test.test');
            });

        $messageProphecy1 = Argument::allOf(
            Argument::type(\Swift_Message::class),
            Argument::which('getSubject', 'subject'),
            Argument::which('getBody', 'body'),
            Argument::which('getFrom', ['no-reply@test.test'  => 'NoReply']),
            Argument::which('getTo', ['john.doe@test.test' => null])
        );

        $messageProphecy2 = Argument::allOf(
            Argument::type(\Swift_Message::class),
            Argument::which('getSubject', 'subject'),
            Argument::which('getBody', 'body'),
            Argument::which('getFrom', [
                'no-reply@test.test'  => 'NoReply',
                'no-reply2@test.test' => 'NoReply2'
            ]),
            Argument::which('getTo', ['john.doe@test.test' => null])
        );

        $this->mailer->send($messageProphecy1)
            ->shouldBeCalledTimes(1);

        $this->mailer->send($messageProphecy2)
            ->shouldBeCalledTimes(1);

        $channel = $this->createChannel([]);

        $channel->handle($delivery1);
        $channel->handle($delivery2);
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
                true,
            ],
            [
                new TwilioRecipient('+330601020304'),
                false,
            ],
            [
                'not an email',
                false,
            ],
            [
                'john.doe@acme.org',
                true,
            ],
            [
                new MobileRecipient(['foo', 'bar']),
                false,
            ],
        ];
    }
}
