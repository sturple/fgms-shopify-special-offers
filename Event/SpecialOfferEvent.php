<?php

namespace Fgms\SpecialOffersBundle\Event;

/**
 * An event which has an associated SpecialOffer
 * entity.
 */
class SpecialOfferEvent extends \Symfony\Component\EventDispatcher\Event
{
    private $offer;

    /**
     * Creates a new SpecialOfferEvent.
     *
     * @param SpecialOffer $offer
     */
    public function __construct(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        $this->offer = $offer;
    }

    /**
     * Gets the associated SpecialOffer entity.
     *
     * @return SpecialOffer
     */
    public function getSpecialOffer()
    {
        return $this->offer;
    }
}
