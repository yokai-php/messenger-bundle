<?php

namespace MessengerBundle\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use MessengerBundle\Entity\Notification;

/**
 * @author Yann Eugoné <yann.eugone@gmail.com>
 */
class NotificationRepository
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
     * @param Notification $notification
     */
    public function setNotificationAsDelivered(Notification $notification)
    {
        $notification->setDelivered();
        $this->manager->persist($notification);
        $this->manager->flush($notification);
    }

    /**
     * @param QueryBuilder $builder
     * @param object       $recipient
     *
     * @return Notification[]
     */
    public function addRecipientConditions(QueryBuilder $builder, $recipient)
    {
        $alias = $builder->getRootAliases()[0];
        $builder
            ->where(
                $builder->expr()->andX(
                    $builder->expr()->eq($alias . '.recipientClass', ':class'),
                    $builder->expr()->eq($alias . '.recipientId', ':id')
                )
            )
            ->setParameter('class', ClassUtils::getClass($recipient))
            ->setParameter('id', $recipient->getId())
        ;
    }

    /**
     * @param object $recipient
     *
     * @return Notification[]
     */
    public function countUndeliveredRecipientNotification($recipient)
    {
        $builder = $this->manager->createQueryBuilder();
        $builder
            ->from(Notification::class, 'notification')
            ->select('COUNT(notification)')
        ;
        $this->addRecipientConditions($builder, $recipient);
        $builder->andWhere($builder->expr()->isNull('messenger.deliveredAt'));

        return intval($builder->getQuery()->getSingleScalarResult());
    }
}
