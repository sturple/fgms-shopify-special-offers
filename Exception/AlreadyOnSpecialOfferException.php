<?php

namespace Fgms\SpecialOffersBundle\Exception;

/**
 * Thrown when an attempt is made to apply a
 * SpecialOffer to an item which already has a
 * SpecialOffer applied to it.
 */
class AlreadyOnSpecialOfferException extends ItemSpecialOfferStrategyException
{
    public function __construct($id, \Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        parent::__construct(
            sprintf(
                '%d is already on special offer (attempted to apply SpecialOffer %d)',
                $id,
                $offer->getId()
            ),
            $id,
            $offer
        );
    }
}
