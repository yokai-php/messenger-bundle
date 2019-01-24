<?php

namespace Yokai\MessengerBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Yokai\MessengerBundle\Entity\Notification;
use Yokai\MessengerBundle\Recipient\IdentifierRecipientInterface;

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
     * @param QueryBuilder                 $builder
     * @param IdentifierRecipientInterface $recipient
     *
     * @return QueryBuilder
     */
    public function addRecipientConditions(QueryBuilder $builder, IdentifierRecipientInterface $recipient)
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
     * @param IdentifierRecipientInterface  $recipient
     *
     * @return int
     */
    public function countUndeliveredRecipientNotification(IdentifierRecipientInterface $recipient)
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
     * @param IdentifierRecipientInterface $recipient
     *
     * @return Notification[]
     */
    public function findUndeliveredRecipientNotification(IdentifierRecipientInterface $recipient)
    {
        $builder = $this->createQueryBuilder('notification');

        $this->addRecipientConditions($builder, $recipient);

        $builder->andWhere($builder->expr()->isNull('notification.deliveredAt'));

        return $builder->getQuery()->getResult();
    }

    /**
     * @param IdentifierRecipientInterface $recipient
     *
     * @return Notification[]
     */
    public function findAllForRecipient(IdentifierRecipientInterface $recipient)
    {
        $builder = $this->createQueryBuilder('notification');

        $this->addRecipientConditions($builder, $recipient);

        return $builder->getQuery()->getResult();
    }
}
