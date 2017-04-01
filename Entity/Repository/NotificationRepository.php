<?php

namespace Yokai\MessengerBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Yokai\MessengerBundle\Entity\Notification;
use Yokai\MessengerBundle\Recipient\DoctrineRecipientInterface;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class NotificationRepository extends EntityRepository
{
    /**
     * @param Notification $notification
     */
    public function setNotificationAsDelivered(Notification $notification)
    {
        $notification->setDelivered();
        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush($notification);
    }

    /**
     * @param QueryBuilder               $builder
     * @param DoctrineRecipientInterface $recipient
     *
     * @return QueryBuilder
     */
    public function addRecipientConditions(QueryBuilder $builder, DoctrineRecipientInterface $recipient)
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

        return $builder;
    }

    /**
     * @param DoctrineRecipientInterface  $recipient
     *
     * @return int
     */
    public function countUndeliveredRecipientNotification(DoctrineRecipientInterface $recipient)
    {
        $builder = $this->createQueryBuilder('notification');
        $builder
            ->select('COUNT(notification)')
        ;
        $this->addRecipientConditions($builder, $recipient);
        $builder->andWhere($builder->expr()->isNull('notification.deliveredAt'));

        return intval($builder->getQuery()->getSingleScalarResult());
    }

    /**
     * @param DoctrineRecipientInterface $recipient
     *
     * @return Notification[]
     */
    public function findUndeliveredRecipientNotification(DoctrineRecipientInterface $recipient)
    {
        $builder = $this->createQueryBuilder('notification');

        $this->addRecipientConditions($builder, $recipient);

        $builder->andWhere($builder->expr()->isNull('notification.deliveredAt'));

        return $builder->getQuery()->getResult();
    }

    /**
     * @param DoctrineRecipientInterface $recipient
     *
     * @return Notification[]
     */
    public function findAllForRecipient(DoctrineRecipientInterface $recipient)
    {
        $builder = $this->createQueryBuilder('notification');

        $this->addRecipientConditions($builder, $recipient);

        return $builder->getQuery()->getResult();
    }
}
