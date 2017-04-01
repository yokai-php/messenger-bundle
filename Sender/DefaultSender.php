<?php

namespace Yokai\MessengerBundle\Sender;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SplPriorityQueue;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface as OptionsResolverException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Channel\ChannelInterface;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Exception\BadConfigurationException;
use Yokai\MessengerBundle\Exception\BadMethodCallException;
use Yokai\MessengerBundle\Exception\ChannelHandleException;
use Yokai\MessengerBundle\Exception\ExceptionInterface;
use Yokai\MessengerBundle\Helper\ContentBuilder;
use Yokai\MessengerBundle\Message;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class DefaultSender implements SenderInterface
{
    /**
     * @var ContentBuilder
     */
    private $contentBuilder;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $channels;

    /**
     * @var array
     */
    private $channelNames;

    /**
     * @var array
     */
    private $messages;

    /**
     * @param ContentBuilder  $contentBuilder
     * @param bool            $debug
     * @param LoggerInterface $logger
     */
    public function __construct(ContentBuilder $contentBuilder, $debug, LoggerInterface $logger = null)
    {
        $this->contentBuilder = $contentBuilder;
        $this->debug = $debug;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param ChannelInterface $channel
     * @param string           $name
     * @param int              $priority
     */
    public function addChannel(ChannelInterface $channel, $name, $priority)
    {
        $oid = spl_object_hash($channel);

        $this->channels[$name] = [
            'object' => $channel,
            'priority' => $priority,
        ];
        $this->channelNames[$oid] = $name;
    }

    /**
     * @param Message $message
     * @param string  $channel
     */
    public function addMessage(Message $message, $channel)
    {
        //Check the channel was previously registered
        if (!isset($this->channels[$channel])) {
            throw BadMethodCallException::createMissingChannel($channel);
        }

        if (!isset($this->messages[$message->getId()])) {
            //Initialize the message by ID hash
            $this->messages[$message->getId()] = [
                'object' => $message,
                'channels' => new SplPriorityQueue(),
            ];
        }

        //Insert the channel in the prioritized queue
        $this->messages[$message->getId()]['channels']->insert(
            $this->channels[$channel]['object'],
            $this->channels[$channel]['priority']
        );
    }

    /**
     * @inheritdoc
     */
    public function send($message, $recipient, array $parameters = [], array $attachments = [])
    {
        //Check that the message is registered
        if (!isset($this->messages[$message])) {
            throw BadMethodCallException::createMissingMessage($message);
        }

        //Retrieve sorted channels for this message
        $channels = iterator_to_array(clone $this->messages[$message]['channels']);
        /* @var $channels ChannelInterface[] */

        //Retrieve message object
        $message = $this->messages[$message]['object'];
        /* @var $message Message */

        try {

            //Iterate over message channels and trigger process
            foreach ($channels as $channel) {
                $oid = spl_object_hash($channel);

                if (!$channel->supports($recipient)) {
                    if ($this->debug) {
                        throw ChannelHandleException::unsupportedRecipient($channel, $message, $recipient);
                    }

                    $this->logger->error(
                        'The recipient is not supported by channel.',
                        [
                            'channel' => $this->channelNames[$oid],
                            'message' => $message->getId(),
                            'parameters' => $parameters,
                        ]
                    );

                    continue;
                }

                //Fetch options of the message for the channel
                $options = $message->getOptions(
                    $this->channelNames[$oid]
                );

                try {
                    //Configure the content builder with message options for this channel
                    $this->contentBuilder->configure($options);
                } catch (OptionsResolverException $exception) {
                    throw BadConfigurationException::create($exception);
                }

                $resolver = new OptionsResolver();
                $channel->configure($resolver);

                try {
                    //Resolve options of the message for this channel
                    $options = $resolver->resolve(
                        array_intersect_key(
                            $options,
                            array_flip($resolver->getDefinedOptions())
                        )
                    );
                } catch (OptionsResolverException $exception) {
                    throw BadConfigurationException::create($exception);
                }

                //Create a delivery object that contains all information about the message to send
                $delivery = new Delivery(
                    $message->getId(), //Message identifier
                    $recipient, //Message recipient
                    $options, //Options for this channel
                    $this->contentBuilder->getSubject($parameters), //Message subject
                    $this->contentBuilder->getBody($parameters), //Message body
                    $parameters, //Provided parameters
                    $attachments //Provided attachments
                );

                try {
                    //Handle message delivery for this channel
                    $channel->handle($delivery);
                } catch (\Exception $exception) {
                    throw ChannelHandleException::createOnException($channel, $delivery, $exception);
                }
            }

        } catch (ExceptionInterface $exception) {

            $this->logger->error(
                $exception->getMessage(),
                [
                    'message' => $message->getId(),
                    'parameters' => $parameters,
                ]
            );

            if ($this->debug) {
                throw $exception;
            }

        } catch (\Exception $exception) {

            $this->logger->critical(
                sprintf(
                    'An error has occurred during the notification process. Exception : %s - %s',
                    get_class($exception),
                    $exception->getMessage()
                ),
                [
                    'message' => $message->getId(),
                    'parameters' => $parameters,
                ]
            );

            if ($this->debug) {
                throw $exception;
            }

        }
    }
}
