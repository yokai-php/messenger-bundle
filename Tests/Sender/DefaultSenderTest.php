<?php

namespace Yokai\MessengerBundle\Tests\Sender;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Channel\ChannelInterface;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Exception\BadConfigurationException;
use Yokai\MessengerBundle\Exception\ChannelHandleException;
use Yokai\MessengerBundle\Helper\ContentBuilder;
use Yokai\MessengerBundle\Message;
use Yokai\MessengerBundle\Sender\DefaultSender;
use Yokai\MessengerBundle\Tests\Fixtures\OptionsResolverException;
use Yokai\MessengerBundle\Tests\Fixtures\Recipient\DummyRecipient;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class DefaultSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $contentBuilder;

    /**
     * @var ObjectProphecy
     */
    private $logger;

    protected function setUp()
    {
        $this->contentBuilder = $this->prophesize(ContentBuilder::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    protected function tearDown()
    {
        unset(
            $this->contentBuilder,
            $this->logger
        );
    }

    protected function createSender($debug = true)
    {
        return new DefaultSender(
            $this->contentBuilder->reveal(),
            $debug,
            $this->logger->reveal()
        );
    }

    protected function createChannel()
    {
        return $this->prophesize(ChannelInterface::class);
    }

    protected function createMessage($id, array $defaults = [])
    {
        return new Message($id, $defaults);
    }

    /**
     * @expectedException \Yokai\MessengerBundle\Exception\BadMethodCallException
     */
    public function testAddMessageToUnknownChannelThrowException()
    {
        $sender = $this->createSender();

        $sender->addChannel($this->createChannel()->reveal(), 'test_channel', 1);

        $sender->addMessage($this->createMessage('test_message'), 'channel_that_do_not_exists');
    }

    /**
     * @expectedException \Yokai\MessengerBundle\Exception\BadMethodCallException
     */
    public function testSendingUnknownMessageThrowException()
    {
        $sender = $this->createSender();

        $sender->addChannel($this->createChannel()->reveal(), 'test_channel', 1);
        $sender->addMessage($this->createMessage('test_message'), 'test_channel');
        $sender->send('message_that_do_not_exists', new DummyRecipient(), []);
    }

    /**
     * @dataProvider toggleDebugProvider
     */
    public function testMisconfiguredContentBuilderIsErroringProcess($debug)
    {
        if ($debug) {
            $this->setExpectedException(BadConfigurationException::class);
        }

        $channel = $this->createChannel();
        $channel->supports(Argument::type(DummyRecipient::class))
            ->willReturn(true);

        $exception = new OptionsResolverException('that fail...');

        $this->contentBuilder->configure([])
            ->shouldBeCalled()
            ->willThrow($exception);

        $this->logger->error(
            'The provided configuration is invalid. Exception : '.OptionsResolverException::class.' - that fail...',
            [ 'message' => 'test_message', 'parameters' => [] ]
        )
            ->shouldBeCalled();

        $sender = $this->createSender($debug);

        $sender->addChannel($channel->reveal(), 'test_channel', 1);
        $sender->addMessage($this->createMessage('test_message'), 'test_channel');

        $sender->send('test_message', new DummyRecipient());
    }

    /**
     * @dataProvider toggleDebugProvider
     */
    public function testMisconfiguredChannelIsErroringProcess($debug)
    {
        if ($debug) {
            $this->setExpectedException(BadConfigurationException::class);
        }

        $channel = $this->createChannel();
        $channel->supports(Argument::type(DummyRecipient::class))
            ->willReturn(true);
        $channel->configure(Argument::type(OptionsResolver::class))
            ->shouldBeCalled()
            ->will(function ($args) {
                $args[0]->setRequired(['option_that_is_not_set']);
            });

        $this->logger->error(
            'The provided configuration is invalid. Exception : '.MissingOptionsException::class.' - The required option "option_that_is_not_set" is missing.',
            [ 'message' => 'test_message', 'parameters' => [] ]
        )
            ->shouldBeCalled();

        $sender = $this->createSender($debug);

        $sender->addChannel($channel->reveal(), 'test_channel', 1);
        $sender->addMessage($this->createMessage('test_message'), 'test_channel');

        $sender->send('test_message', new DummyRecipient());
    }

    /**
     * @dataProvider toggleDebugProvider
     */
    public function testExceptionInChannelIsErroringProcess($debug)
    {
        if ($debug) {
            $this->setExpectedException(ChannelHandleException::class);
        }

        $deliveryProphecy = Argument::allOf(
            Argument::type(Delivery::class),
            Argument::which('getMessage', 'test_message'),
            Argument::which('getOptions', []),
            Argument::which('getSubject', 'subject'),
            Argument::which('getBody', 'body'),
            Argument::which('getParameters', [])
        );

        $this->contentBuilder->configure([])
            ->shouldBeCalled();
        $this->contentBuilder->getSubject([])
            ->shouldBeCalled()
            ->willReturn('subject');
        $this->contentBuilder->getBody([])
            ->shouldBeCalled()
            ->willReturn('body');

        $channel = $this->createChannel();
        $channel->supports(Argument::type(DummyRecipient::class))
            ->willReturn(true);
        $channel->configure(Argument::type(OptionsResolver::class))
            ->shouldBeCalled();
        $channel->handle($deliveryProphecy)
            ->shouldBeCalled()
            ->willThrow(new \Exception('channel failed'));

        $this->logger->error(
            'An error has occurred during the notification process of message "test_message" for channel "'.get_class($channel->reveal()).'". Exception : Exception - channel failed',
            [ 'message' => 'test_message', 'parameters' => [] ]
        )
            ->shouldBeCalled();

        $sender = $this->createSender($debug);

        $sender->addChannel($channel->reveal(), 'test_channel', 1);
        $sender->addMessage($this->createMessage('test_message'), 'test_channel');

        $sender->send('test_message', new DummyRecipient());
    }

    /**
     * @dataProvider toggleDebugProvider
     */
    public function testUnexpectedExceptionIsErroringProcess($debug)
    {
        if ($debug) {
            $this->setExpectedException(\Exception::class);
        }

        $exception = new \Exception('that fail...');

        $channel = $this->createChannel();
        $channel->supports(Argument::type(DummyRecipient::class))
            ->willReturn(true);
        $channel->configure(Argument::type(OptionsResolver::class))
            ->shouldBeCalled()
            ->willThrow($exception);

        $this->logger->critical(
            'An error has occurred during the notification process. '.\Exception::class.' : Exception - that fail...',
            [ 'message' => 'test_message', 'parameters' => [] ]
        )
            ->shouldBeCalled();

        $sender = $this->createSender($debug);

        $sender->addChannel($channel->reveal(), 'test_channel', 1);
        $sender->addMessage($this->createMessage('test_message'), 'test_channel');

        $sender->send('test_message', new DummyRecipient());
    }

    /**
     * @dataProvider toggleDebugProvider
     */
    public function testUnsupportedRecipientIsErroringProcess($debug)
    {
        if ($debug) {
            $this->setExpectedException(ChannelHandleException::class);
        }

        $channel = $this->createChannel();
        $channel->supports(Argument::type(DummyRecipient::class))
            ->willReturn(false);

        if (!$debug) {
            $this->logger->error(
                'The recipient is not supported by channel.',
                [ 'channel' => 'test_channel', 'message' => 'test_message', 'parameters' => [] ]
            )
                ->shouldBeCalled();
        } else {
            $this->logger->error(
                'The recipient "'.DummyRecipient::class.'" is not supported by channel "'.get_class($channel->reveal()).'" for message "test_message".',
                [ 'message' => 'test_message', 'parameters' => [] ]
            )
                ->shouldBeCalled();
        }

        $sender = $this->createSender($debug);

        $sender->addChannel($channel->reveal(), 'test_channel', 1);
        $sender->addMessage($this->createMessage('test_message'), 'test_channel');

        $sender->send('test_message', new DummyRecipient());
    }

    public function testMessageIsDistributedOverMultipleChannels()
    {
        $channel1 = $this->createChannel();
        $channel1->supports(Argument::type(DummyRecipient::class))
            ->willReturn(true);
        $channel1->configure(Argument::type(OptionsResolver::class))
            ->shouldBeCalled();
        $channel1->handle(Argument::type(Delivery::class))
            ->shouldBeCalled();

        $channel2 = $this->createChannel();
        $channel2->supports(Argument::type(DummyRecipient::class))
            ->willReturn(true);
        $channel2->configure(Argument::type(OptionsResolver::class))
            ->shouldBeCalled();
        $channel2->handle(Argument::type(Delivery::class))
            ->shouldBeCalled();

        $sender = $this->createSender();

        $message = $this->createMessage('test_message');

        $sender->addChannel($channel1->reveal(), 'test_channel_1', 1);
        $sender->addChannel($channel2->reveal(), 'test_channel_2', 2);
        $sender->addMessage($message, 'test_channel_1');
        $sender->addMessage($message, 'test_channel_2');

        $sender->send('test_message', new DummyRecipient());
    }

    public function testConfigurationCanBeDifferentOverMultipleChannels()
    {
        $delivery1Prophecy = Argument::allOf(
            Argument::type(Delivery::class),
            Argument::which('getOptions', [
                'channel_1' => 'channel_1',
                'default' => true,
            ])
        );
        $channel1 = $this->createChannel();
        $channel1->supports(Argument::type(DummyRecipient::class))
            ->willReturn(true);
        $channel1->configure(Argument::type(OptionsResolver::class))
            ->shouldBeCalled()
            ->will(function ($args) {
                $args[0]->setRequired(['default', 'channel_1']);
            });
        $channel1->handle($delivery1Prophecy)
            ->shouldBeCalled();

        $delivery2Prophecy = Argument::allOf(
            Argument::type(Delivery::class),
            Argument::which('getOptions', [
                'channel_2' => 'channel_2',
                'default' => false,
            ])
        );
        $channel2 = $this->createChannel();
        $channel2->supports(Argument::type(DummyRecipient::class))
            ->willReturn(true);
        $channel2->configure(Argument::type(OptionsResolver::class))
            ->shouldBeCalled()
            ->will(function ($args) {
                $args[0]->setRequired(['default', 'channel_2']);
            });
        $channel2->handle($delivery2Prophecy)
            ->shouldBeCalled();

        $sender = $this->createSender();

        $sender->addChannel($channel1->reveal(), 'test_channel_1', 1);
        $sender->addChannel($channel2->reveal(), 'test_channel_2', 2);

        $message = $this->createMessage('test_message', ['default' => true]);
        $message->setOptions('test_channel_1', ['channel_1' => 'channel_1']);
        $message->setOptions('test_channel_2', ['default' => false, 'channel_2' => 'channel_2']);

        $sender->addMessage($message, 'test_channel_1');
        $sender->addMessage($message, 'test_channel_2');

        $sender->send('test_message', new DummyRecipient());
    }

    public function toggleDebugProvider()
    {
        return [
            [
                true
            ],
            [
                false
            ]
        ];
    }
}
