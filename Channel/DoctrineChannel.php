<?php

namespace MessengerBundle\Channel;

use Doctrine\ORM\EntityManager;
use MessengerBundle\Delivery;
use MessengerBundle\Entity\Notification;
use MessengerBundle\Recipient\DoctrineRecipientInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class DoctrineChannel implements ChannelInterface
{
    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var array
     */
    private $defaults;

    /**
     * @param RegistryInterface $doctrine
     * @param array             $defaults
     */
    public function __construct(RegistryInterface $doctrine, array $defaults)
    {
        $this->doctrine = $doctrine;
        $this->defaults = $defaults;
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
        //todo
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

        /* @var $manager EntityManager */
        $manager = $this->doctrine->getManagerForClass(Notification::class);
        $manager->persist($notification);
        $manager->flush($notification);
    }
}
