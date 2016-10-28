<?php

namespace Fgms\SpecialOffersBundle\Exception;

/**
 * A base class for exceptions thrown by a
 * SpecialOfferStrategyInterface when an error
 * is encountered relating to a single item.
 */
class ItemSpecialOfferStrategyException extends SpecialOfferStrategyException
{
    private $id;
    private $offer;

    /**
     * Creates a new ItemSpecialOfferStrategyException.
     *
     * @param int $id
     *  The ID of the item which was already on special
     *  offer.
     * @param SpecialOffer $offer
     */
    public function __construct($msg, $id, \Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        parent::__construct($msg);
        $this->id = $id;
        $this->offer = $offer;
    }

    /**
     * Obtains the ID of the item which was already
     * on special offer.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Obtains the SpecialOffer entity which was being
     * applied when this exception was thrown.
     *
     * @return SpecialOffer
     */
    public function getSpecialOffer()
    {
        return $this->offer;
    }
}
