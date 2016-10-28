<?php

namespace Fgms\SpecialOffersBundle\Exception;

/**
 * Thrown when an attempt is made to revert a
 * SpecialOffer on an item which does not have
 * a SpecialOffer applied to it.
 */
class NotOnSpecialOfferException extends ItemSpecialOfferStrategyException
{
}
