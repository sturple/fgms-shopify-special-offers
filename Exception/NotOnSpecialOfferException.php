<?php

namespace Fgms\SpecialOffersBundle\Exception;

/**
 * Thrown when an attempt is made to revert a
 * SpecialOffer on an item which does not have
 * a SpecialOffer applied to it.
 */
class NotOnSpecialOfferException extends ItemSpecialOfferStrategyException
{
    public function __construct($id, \Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        parent::__construct(
            sprintf(
                '%d is not on special offer (attempted to revert SpecialOffer %d)',
                $id,
                $offer->getId()
            ),
            $id,
            $offer
        );
    }
}
