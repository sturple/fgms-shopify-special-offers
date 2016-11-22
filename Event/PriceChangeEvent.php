<?php

namespace Fgms\SpecialOffersBundle\Event;

/**
 * An event which has associated PriceChange
 * entities.
 */
class PriceChangeEvent extends SpecialOfferEvent
{
    private $changes;

    /**
     * Creates a new PriceChangeEvent.
     *
     * @param SpecialOffer $offer
     * @param array $changes
     */
    public function __construct(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer, array $changes)
    {
        parent::__construct($offer);
        $this->changes = $changes;
    }

    /**
     * Retrieves the associated PriceChange entities.
     *
     * @return array
     */
    public function getPriceChanges()
    {
        return $this->changes;
    }
}
