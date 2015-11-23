<?php

namespace MessengerBundle\Tests\Channel;

use Doctrine\ORM\EntityManagerInterface;
use MessengerBundle\Channel\DoctrineChannel;
use MessengerBundle\Delivery;
use MessengerBundle\Entity\Notification;
use MessengerBundle\Tests\Fixtures\Recipient\DoctrineRecipient;
use MessengerBundle\Tests\Fixtures\Recipient\SwiftmailerRecipient;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class DoctrineChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $doctrine;

    protected function setUp()
    {
        $this->doctrine = $this->prophesize(RegistryInterface::class);
    }

    protected function tearDown()
    {
        unset(
            $this->doctrine
        );
    }

    protected function createChannel(array $defaults)
    {
        return new DoctrineChannel(
            $this->doctrine->reveal(),
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
        ]);
        $channel->configure($resolver);

        $this->assertSame([], $resolver->resolve([]));
    }

    public function testIsCreatingNotificationEntity()
    {
        $recipient = new DoctrineRecipient(1);

        $notificationProphecy = Argument::allOf(
            Argument::type(Notification::class),
            Argument::which('getSubject', 'subject'),
            Argument::which('getBody', 'body'),
            Argument::which('getRecipientClass', DoctrineRecipient::class),
            Argument::which('getRecipientId', 1)
        );

        $manager = $this->prophesize(EntityManagerInterface::class);
        $manager->persist($notificationProphecy)
            ->shouldBeCalled();
        $manager->flush($notificationProphecy)
            ->shouldBeCalled();

        $this->doctrine->getManagerForClass(Notification::class)
            ->shouldBeCalled()
            ->willReturn($manager->reveal());

        $channel = $this->createChannel([]);

        $channel->handle(
            new Delivery(
                'test',
                $recipient,
                [],
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
                new SwiftmailerRecipient('john.doe@acme.org'),
                false,
            ],
            [
                new DoctrineRecipient(1),
                true,
            ],
        ];
    }
}
