<?php

namespace Yokai\MessengerBundle\Channel;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Yokai\MessengerBundle\Delivery;
use Yokai\MessengerBundle\Entity\Notification;
use Yokai\MessengerBundle\Entity\NotificationAttachment;
use Yokai\MessengerBundle\Recipient\DoctrineRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class DoctrineChannel implements ChannelInterface
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritdoc
     */
    public function supports($recipient)
    {
        if (is_object($recipient) && $recipient instanceof DoctrineRecipientInterface) {
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
            ->setDefined(['attachments_path'])
        ;
    }

    /**
     * @inheritdoc
     */
    public function handle(Delivery $delivery)
    {
        $options = $delivery->getOptions();

        $notification = new Notification(
            $delivery->getSubject(),
            $delivery->getBody(),
            $delivery->getRecipient()
        );

        $fs = new Filesystem();
        foreach ($delivery->getAttachments() as $attachment) {
            $fs->copy(
                $attachment->getPathname(),
                sprintf('%s/%s', $options['attachments_path'], $attachment->getBasename())
            );
            $notificationAttachment = new NotificationAttachment($notification, $attachment->getBasename());
            $notification->addNotificationAttachment($notificationAttachment);
        }

        $this->manager->persist($notification);
        $this->manager->flush($notification);
    }
}
