<?php

namespace Fgms\SpecialOffersBundle\Exception;

/**
 * Thrown when an attempt is made to apply a
 * SpecialOffer to an item which already has a
 * SpecialOffer applied to it.
 */
class AlreadyOnSpecialOfferException extends ItemSpecialOfferStrategyException
{
}
