<?php

namespace Yokai\MessengerBundle\Channel;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Channel\Twilio\Factory\ClientFactoryInterface;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Recipient\TwilioRecipientInterface;

/**
 * @author Matthieu Crinquand <matthieu.crinquand@gmail.com>
 */
class TwilioChannel implements ChannelInterface
{
    /**
     * @var ClientFactoryInterface
     */
    private $twilioClientFactory;

    /**
     * @var array
     */
    private $defaults;

    /**
     * @param ClientFactoryInterface $twilioClientFactory
     * @param array $defaults
     */
    public function __construct(
        ClientFactoryInterface $twilioClientFactory,
        array $defaults
    ) {
        $this->twilioClientFactory = $twilioClientFactory;
        $this->defaults = $defaults;
    }

    public function supports($recipient)
    {
        if (is_object($recipient) && $recipient instanceof TwilioRecipientInterface) {
            return true;
        }

        if (is_string($recipient)) {
            return true;
        }

        return false;
    }

    public function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['from'])
            ->setRequired(['api_id'])
            ->setRequired(['api_token'])
        ;

        foreach ($resolver->getDefinedOptions() as $option) {
            if (isset($this->defaults[$option])) {
                $resolver->setDefault($option, $this->defaults[$option]);
            }
        }
    }

    public function handle(Delivery $delivery)
    {
        $recipient = $delivery->getRecipient();
        $options = $delivery->getOptions();

        $client = $this->twilioClientFactory->createClient($options['api_id'], $options['api_token']);

        $phone = $recipient instanceof TwilioRecipientInterface ? $recipient->getPhone() : $recipient;

        $client->messages->create($phone, [
            'from' => $options['from'],
            'body' => $delivery->getBody(),
        ]);
    }
}
