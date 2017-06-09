<?php

namespace Yokai\MessengerBundle\Channel;

use Swift_Mailer;
use Swift_Message;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Channel\Swiftmailer\Configurator\SwiftMessageConfiguratorInterface;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Recipient\SwiftmailerRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class SwiftmailerChannel implements ChannelInterface
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var SwiftMessageConfiguratorInterface
     */
    private $configurator;

    /**
     * @var array
     */
    private $defaults;

    /**
     * @param Swift_Mailer                      $mailer
     * @param SwiftMessageConfiguratorInterface $configurator
     * @param array                             $defaults
     */
    public function __construct(
        Swift_Mailer $mailer,
        SwiftMessageConfiguratorInterface $configurator,
        array $defaults
    ) {
        $this->mailer = $mailer;
        $this->configurator = $configurator;
        $this->defaults = $defaults;
    }

    /**
     * @inheritdoc
     */
    public function supports($recipient)
    {
        if (is_object($recipient) && $recipient instanceof SwiftmailerRecipientInterface) {
            return true;
        }

        if (is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function configure(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['from'])
            //todo
        ;
        foreach ($resolver->getDefinedOptions() as $option) {
            if (isset($this->defaults[$option])) {
                $resolver->setDefault($option, $this->defaults[$option]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function handle(Delivery $delivery)
    {
        $mail = new Swift_Message();

        $this->configurator->configure($mail, $delivery);

        $this->mailer->send($mail);
    }
}
