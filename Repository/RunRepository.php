<?php

namespace Fgms\SpecialOffersBundle\Repository;

class RunRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Retrieves the most recent Run entity.
     *
     * @return Run|null
     *  The most recent Run entity unless there are no
     *  Run entities in which case null is returned.
     */
    public function getLast()
    {
        $qb = $this->createQueryBuilder('r');
        $qb->orderBy('r.when','DESC')
            ->setMaxResults(1);
        $q = $qb->getQuery();
        $arr = $q->getResult();
        if (count($arr) === 0) return null;
        return $arr[0];
    }
}
