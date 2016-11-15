<?php

namespace Fgms\SpecialOffersBundle\Repository;

class SpecialOfferRepository extends \Doctrine\ORM\EntityRepository
{
    private function executeRangeQuery(\DateTime $to, \Fgms\SpecialOffersBundle\Entity\Store $store = null, $property, $status)
    {
        $qb = $this->createQueryBuilder('so');
        $qb->andWhere($qb->expr()->lte('so.' . $property,':to'))
            ->setParameter('to',\Fgms\SpecialOffersBundle\Utility\DateTime::toDoctrine($to))
            ->andWhere($qb->expr()->eq('so.status',':status'))
            ->setParameter('status',$status);
        if (!is_null($store)) {
            $qb->innerJoin(\Fgms\SpecialOffersBundle\Entity\Store::class,'st')
                ->andWhere($qb->expr()->eq('st.id',':stid'))
                ->setParameter('stid',$store->getId());
        }
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
}
