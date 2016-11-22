<?php

namespace Fgms\SpecialOffersBundle\Shopify\Exception;

/**
 * Thrown when data returned by the Shopify API is of
 * an unexpected type.
 */
class TypeMismatchException extends DataException
{
    /**
     * Creates a new TypeMismatchException.
     *
     * @param string $expected
     *  A string giving the expected type.
     * @param mixed $actual
     *  The actual value.
     * @param string $path
     *  The JSON Pointer path to the data.
     * @param string $json
     *  The JSON string of the Shopify API response
     *  which is being handled.
     */
    public function __construct($expected, $actual, $path, $json)
    {
        parent::__construct(
            sprintf(
                'Expected "%s" to be %s (got %s): %s',
                $path,
                $expected,
                gettype($actual),
                $json
            )
        );
    }
}
