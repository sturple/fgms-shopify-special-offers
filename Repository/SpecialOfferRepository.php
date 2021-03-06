<?php

namespace Fgms\SpecialOffersBundle\Repository;

class SpecialOfferRepository extends \Doctrine\ORM\EntityRepository
{
    private function addStore(\Doctrine\ORM\QueryBuilder $qb, \Fgms\SpecialOffersBundle\Entity\Store $store = null)
    {
        if (is_null($store)) return;
        $qb->innerJoin('so.store','st')
            ->andWhere($qb->expr()->eq('st.id',':stid'))
            ->setParameter('stid',$store->getId());
    }

    private function addMax(\Doctrine\ORM\QueryBuilder $qb, $max = null)
    {
        if (is_null($max)) return;
        $qb->setMaxResults($max);
    }

    private function executeRangeQuery(\DateTime $to, \Fgms\SpecialOffersBundle\Entity\Store $store = null, $property, $status)
    {
        $qb = $this->createQueryBuilder('so');
        $qb->andWhere($qb->expr()->lte('so.' . $property,':to'))
            ->setParameter('to',\Fgms\SpecialOffersBundle\Utility\DateTime::toDoctrine($to))
            ->andWhere($qb->expr()->eq('so.status',':status'))
            ->setParameter('status',$status);
        $this->addStore($qb,$store);
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
     * @param Store|null $store
     *  A Store entity representing the Shopify store.  Defaults to
     *  null.  If null retrieves all SpecialOffer entities regardless
     *  of store.
     *
     * @return array
     *  A collection of SpecialOffer entities which start before or
     *  at \em to.
     */
    public function getStarting(\DateTime $to, \Fgms\SpecialOffersBundle\Entity\Store $store = null)
    {
        return $this->executeRangeQuery($to,$store,'start','pending');
    }

    /**
     * Obtains all SpecialOffer entities which end before or
     * at a certain date and time and which have already started
     * and not already ended.
     *
     * @param DateTime $to
     *  The date and time at which the search shall end, inclusive.
     * @param string $store
     *  A Store entity representing the Shopify store.  Defaults to
     *  null.  If null retrieves all SpecialOffer entities regardless
     *  of store.
     *
     * @return array
     *  A collection of SpecialOffer entities which end before or
     *  at \em to.
     */
    public function getEnding(\DateTime $to, \Fgms\SpecialOffersBundle\Entity\Store $store = null)
    {
        return $this->executeRangeQuery($to,$store,'end','active');
    }

    /**
     * Obtains all SpecialOffer entities with a certain status.
     *
     * If retrieving active entities they will be ordered by when
     * they shall be applied with sooner coming first.  Otherwise
     * they will be ordered by when they expire with sooner coming
     * first.
     *
     * @param string $status
     * @param Store|null $store
     *  A Store entity representing the Shopify store.  Defaults to
     *  null.  If null retrieves all SpecialOffer entities regardless
     *  of store.
     * @param int|null $max
     *  The maximum number of results to retrieve.  Defaults to null
     *  in which case an unlimited number of results will be retrieved.
     *
     * @return array
     */
    public function getByStatus($status, \Fgms\SpecialOffersBundle\Entity\Store $store = null, $max = null)
    {
        $qb = $this->createQueryBuilder('so');
        $this->addStore($qb,$store);
        $this->addMax($qb,$max);
        $qb->andWhere($qb->expr()->eq('so.status',':status'))
            ->setParameter('status',$status)
            ->addOrderBy(
                ($status === 'active') ? 'so.start' : 'so.end',
                'DESC'
            );
        $q = $qb->getQuery();
        return $q->getResult();
    }

    /**
     * Obtains the SpecialOffer entity with a certain ID.
     *
     * @param int $id
     * @param Store|null $store
     *  A Store entity representing the Shopify store.  Defaults
     *  to null.  If this is provided a SpecialOffer entity shall
     *  only be returned if it is associated with this Store entity.
     *
     * @return SpecialOffer|null
     */
    public function getById($id, \Fgms\SpecialOffersBundle\Entity\Store $store = null)
    {
        $qb = $this->createQueryBuilder('so');
        $this->addStore($qb,$store);
        $qb->andWhere($qb->expr()->eq('so.id',':id'))
            ->setParameter('id',$id);
        $q = $qb->getQuery();
        $arr = $q->getResult();
        if (count($arr) !== 1) return null;
        return $arr[0];
    }
}
