<?php

namespace MessengerBundle\Channel;

use Doctrine\ORM\EntityManager;
use MessengerBundle\Delivery;
use MessengerBundle\Entity\Notification;
use MessengerBundle\Recipient\DoctrineRecipientInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
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
     * {@inheritdoc}
     */
    public function supports($recipient)
    {
        if (is_object($recipient) && $recipient instanceof DoctrineRecipientInterface) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Delivery $delivery)
    {
        $notification = new Notification(
            $delivery->getSubject(),
            $delivery->getBody(),
            $delivery->getRecipient()
        );

        $this->manager->persist($notification);
        $this->manager->flush($notification);
    }
}
