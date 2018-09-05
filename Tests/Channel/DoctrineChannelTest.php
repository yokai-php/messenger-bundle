<?php

namespace Yokai\MessengerBundle\Tests\Channel;

use Doctrine\ORM\EntityManager;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Channel\DoctrineChannel;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Entity\Notification;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\DoctrineRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\MobileRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\SwiftmailerRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\TwilioRecipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class DoctrineChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $manager;

    protected function setUp()
    {
        $this->manager = $this->prophesize(EntityManager::class);
    }

    protected function tearDown()
    {
        unset(
            $this->doctrine
        );
    }

    protected function createChannel(array $defaults)
    {
        return new DoctrineChannel($this->manager->reveal());
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
        ]);
        $channel->configure($resolver);

        $this->assertSame([], $resolver->resolve([]));
    }

    public function testIsCreatingNotificationEntity()
    {
        $recipient = new DoctrineRecipient('1');

        $notificationProphecy = Argument::allOf(
            Argument::type(Notification::class),
            Argument::which('getSubject', 'subject'),
            Argument::which('getBody', 'body'),
            Argument::which('getRecipientClass', DoctrineRecipient::class),
            Argument::which('getRecipientId', '1')
        );

        $this->manager->persist($notificationProphecy)
            ->shouldBeCalled();
        $this->manager->flush($notificationProphecy)
            ->shouldBeCalled();

        $channel = $this->createChannel([]);

        $resolver = new OptionsResolver();
        $channel->configure($resolver);
        $parameters = $resolver->resolve([]);

        $channel->handle(
            new Delivery(
                'test',
                $recipient,
                $parameters,
                'subject',
                'body',
                [
                ],
                [
                ]
            )
        );
    }

    public function supportsRecipientProvider()
    {
        return [
            [
                new SwiftmailerRecipient('john.doe@acme.org'),
                false,
            ],
            [
                new TwilioRecipient('+330601020304'),
                false,
            ],
            [
                new DoctrineRecipient('1'),
                true,
            ],
            [
                new MobileRecipient(['foo', 'bar']),
                false,
            ],
        ];
    }
}
