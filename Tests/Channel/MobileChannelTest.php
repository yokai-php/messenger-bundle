<?php

namespace Yokai\MessengerBundle\Tests\Channel;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sly\NotificationPusher\Adapter\AdapterInterface;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Model\Device;
use Sly\NotificationPusher\Model\Message;
use Sly\NotificationPusher\Model\Push;
use Sly\NotificationPusher\PushManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Channel\MobileChannel;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\DoctrineRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\MobileRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\SwiftmailerRecipient;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\TwilioRecipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class MobileChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $manager;

    /**
     * @var ObjectProphecy[]
     */
    private $adapters;

    protected function setUp()
    {
        $this->manager = $this->prophesize(PushManager::class);
        $this->adapters = [];
    }

    protected function tearDown()
    {
        unset(
            $this->doctrine,
            $this->adapters
        );
    }

    protected function createChannel(array $defaults)
    {
        $adapters = [];
        foreach ($this->adapters as $adapter) {
            $adapters[] = $adapter->reveal();
        }

        return new MobileChannel(
            $this->manager->reveal(),
            $adapters
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

    public function testIsPushingToPushManager()
    {
        $recipient = new MobileRecipient(['foo', 'bar']);

        $fooAdapter = $this->createAdapter();
        $fooAdapter->supports('foo')
            ->shouldBeCalled()
            ->willReturn(true);
        $fooAdapter->supports('bar')
            ->shouldBeCalled()
            ->willReturn(false);

        $barAdapter = $this->createAdapter();
        $barAdapter->supports('foo')
            ->shouldBeCalled()
            ->willReturn(false);
        $barAdapter->supports('bar')
            ->shouldBeCalled()
            ->willReturn(true);

        $foobarAdapter = $this->createAdapter();
        $foobarAdapter->supports('foo')
            ->shouldBeCalled()
            ->willReturn(true);
        $foobarAdapter->supports('bar')
            ->shouldBeCalled()
            ->willReturn(true);

        $fooPushProphecy = $this->getPushProphecy(
            $fooAdapter->reveal(),
            function (DeviceCollection $collection) {
                if (1 !== $collection->count()) {
                    return false;
                }

                return $collection->get('foo') instanceof Device;
            }
        );

        $barPushProphecy = $this->getPushProphecy(
            $barAdapter->reveal(),
            function (DeviceCollection $collection) {
                if (1 !== $collection->count()) {
                    return false;
                }

                return $collection->get('bar') instanceof Device;
            }
        );

        $foobarPushProphecy = $this->getPushProphecy(
            $foobarAdapter->reveal(),
            function (DeviceCollection $collection) {
                if (2 !== $collection->count()) {
                    return false;
                }

                return $collection->get('foo') instanceof Device && $collection->get('bar') instanceof Device;
            }
        );

        $this->manager->add($fooPushProphecy)
              ->shouldBeCalledTimes(1);
        $this->manager->add($barPushProphecy)
              ->shouldBeCalledTimes(1);
        $this->manager->add($foobarPushProphecy)
              ->shouldBeCalledTimes(1);

        $this->manager->push()
              ->shouldBeCalledTimes(1);

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
                [],
                []
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
                false,
            ],
            [
                new MobileRecipient(['foo', 'bar']),
                true,
            ],
        ];
    }

    private function createAdapter()
    {
        $adapter = $this->prophesize(AdapterInterface::class);

        $this->adapters[] = $adapter;

        return $adapter;
    }

    private function getPushProphecy($adapter, $assertDeviceCollection)
    {
        return Argument::allOf(
            Argument::type(Push::class),
            Argument::which('getAdapter', $adapter),
            Argument::that(function (Push $push) use ($assertDeviceCollection) {
                $devices = $push->getDevices();
                if (!$devices instanceof DeviceCollection) {
                    return false;
                }

                return $assertDeviceCollection($devices);
            }),
            Argument::that(function (Push $push) {
                $message = $push->getMessage();
                if (!$message instanceof Message) {
                    return false;
                }

                return $message->getText() === 'subject';
            })
        );
    }
}
