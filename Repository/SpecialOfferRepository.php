<?php

namespace Fgms\SpecialOffersBundle\Repository;

class SpecialOfferRepository extends \Doctrine\ORM\EntityRepository
{
    private function addRangeToQueryBuilder(\DateTime $from = null, \DateTime $to = null, $table, $property, \Doctrine\ORM\QueryBuilder $qb)
    {
        if (!is_null($from)) {
            $qb->andWhere($qb->expr()->gt($table . '.' . $property,':from'))
                ->setParameter('from',$from);
        }
        if (!is_null($to)) {
            $qb->andWhere($qb->expr()->lte($table . '.' . $property,':to'))
                ->setParameter('to',$to);
        }
    }

    private function executeRangeQuery(\DateTime $from = null, \DateTime $to = null, $property)
    {
        $qb = $this->createQueryBuilder('so');
        $this->addRangeToQueryBuilder($from,$to,'so',$property,$qb);
        $q = $qb->getQuery();
        return $q->getResult();
    }

    /**
     * Obtains all SpecialOffer entities which start between
     * certain dates and times.
     *
     * @param DateTime|null $from
     *  The time at which the search shall begin, exclusive.  If
     *  null all SpecialOffer entities from \em to until the beginning
     *  of time shall be returned.
     * @param DateTime|null $to
     *  The time at which the search shall end, inclusive.  If null
     *  all SpecialOffer entities from \em from until the end of time
     *  shall be returned.
     *
     * @return array
     *  A collection of SpecialOffer entities which start between
     *  \em from and \em to.
     */
    public function getStarting(\DateTime $from = null, \DateTime $to = null)
    {
        return $this->executeRangeQuery($from,$to,'start');
    }

    /**
     * Obtains all SpecialOffer entities which end between
     * certain dates and times.
     *
     * @param DateTime|null $from
     *  The time at which the search shall begin, exclusive.  If
     *  null all SpecialOffer entities from \em to until the beginning
     *  of time shall be returned.
     * @param DateTime|null $to
     *  The time at which the search shall end, inclusive.  If null
     *  all SpecialOffer entities from \em from until the end of time
     *  shall be returned.
     *
     * @return array
     *  A collection of SpecialOffer entities which end between \em from
     *  and \em to.
     */
    public function getEnding(\DateTime $from = null, \DateTime $to = null)
    {
        return $this->executeRangeQuery($from,$to,'end');
    }
}
