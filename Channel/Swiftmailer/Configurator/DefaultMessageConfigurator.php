<?php

namespace Yokai\MessengerBundle\Channel\Swiftmailer\Configurator;

use Swift_Attachment;
use Swift_Message;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Recipient\SwiftmailerRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class DefaultMessageConfigurator implements SwiftMessageConfiguratorInterface
{
    /**
     * @inheritdoc
     */
    public function configure(Swift_Message $message, Delivery $delivery)
    {
        $recipient = $delivery->getRecipient();

        $options = $delivery->getOptions();

        $message
            ->setSubject($delivery->getSubject())
            ->setFrom($options['from'])
            ->setTo($recipient instanceof SwiftmailerRecipientInterface ? $recipient->getEmail() : $recipient)
            ->setBody($delivery->getBody(), 'text/html')
        ;

        foreach ($delivery->getAttachments() as $file) {
            $message->attach(Swift_Attachment::fromPath($file->getPathname(), $file->getMimeType()));
        }
    }
}
