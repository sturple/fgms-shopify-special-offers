<?php

namespace Fgms\SpecialOffersBundle\Repository;

class SpecialOfferRepository extends \Doctrine\ORM\EntityRepository
{
    private function executeRangeQuery(\DateTime $to, $property, $status)
    {
        $qb = $this->createQueryBuilder('so');
        $qb->andWhere($qb->expr()->lte('so.' . $property,':to'))
            ->setParameter('to',\Fgms\SpecialOffersBundle\Utility\DateTime::toDoctrine($to))
            ->andWhere($qb->expr()->eq('so.status',':status'))
            ->setParameter('status',$status);
        $q = $qb->getQuery();
        return $q->getResult();
    }

    /**
     * Obtains all SpecialOffer entities which start before or
     * at a certain date and time and which have not already been
     * started.
     *
     * @param DateTime $to
     *  The date and time at which the search shall end, inclusive.
     *
     * @return array
     *  A collection of SpecialOffer entities which start before or
     *  at \em to.
     */
    public function getStarting(\DateTime $to)
    {
        return $this->executeRangeQuery($to,'start','pending');
    }

    /**
     * Obtains all SpecialOffer entities which end before or
     * at a certain date and time and which have already started
     * and not already ended.
     *
     * @param DateTime $to
     *  The date and time at which the search shall end, inclusive.
     *
     * @return array
     *  A collection of SpecialOffer entities which end before or
     *  at \em to.
     */
    public function getEnding(\DateTime $to)
    {
        return $this->executeRangeQuery($to,'end','active');
    }
}
