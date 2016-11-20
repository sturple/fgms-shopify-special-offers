<?php

namespace Fgms\SpecialOffersBundle\Shopify\Exception;

/**
 * Thrown when data returned by the Shopify API
 * is missing expected data.
 */
class MissingException extends DataException
{
    /**
     * Creates a new MissingException.
     *
     * @param string $path
     *  The JSON Pointer path to the expected data.
     * @param string $json
     *  The JSON string of the Shopify API response
     *  which is being handled.
     */
    public function __construct($path, $json)
    {
        parent::__construct(
            sprintf(
                'Expected "%s": %s',
                $path,
                $json
            )
        );
    }
}
