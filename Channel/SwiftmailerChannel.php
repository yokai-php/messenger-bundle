<?php

namespace Yokai\MessengerBundle\Channel;

use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Recipient\SwiftmailerRecipientInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class SwiftmailerChannel implements ChannelInterface
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var array
     */
    private $defaults;

    /**
     * @param \Swift_Mailer $mailer
     * @param array         $defaults
     */
    public function __construct(\Swift_Mailer $mailer, array $defaults)
    {
        $this->mailer = $mailer;
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function handle(Delivery $delivery)
    {
        $recipient = $delivery->getRecipient();

        $options = $delivery->getOptions();

        $mail = \Swift_Message::newInstance();
        $mail
            ->setSubject($delivery->getSubject())
            ->setFrom($options['from'])
            ->setTo($recipient instanceof SwiftmailerRecipientInterface ? $recipient->getEmail() : $recipient)
            ->setBody($delivery->getBody(), 'text/html')
        ;

        $this->mailer->send($mail);
    }
}
