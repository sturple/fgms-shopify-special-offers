<?php

namespace Fgms\SpecialOffersBundle\Strategy;

/**
 * An interface which may be implemented to apply and
 * revert SpecialOffer entities.
 */
interface SpecialOfferStrategyInterface
{
    /**
     * Applies a SpecialOffer entity.
     *
     * @param SpecialOffer $offer
     *  This object must not be mutated.
     *
     * @return array
     *  An array of PriceChange entities representing
     *  the changes the method actually performed.
     */
    public function apply(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer);

    /**
     * Reverts a SpecialOffer entity.
     *
     * @param SpecialOffer $offer
     *  This object must not be mutated.
     *
     * @return array
     *  An array of PriceChange entities representing
     *  the changes the method actually performed.
     */
    public function revert(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer);
}
