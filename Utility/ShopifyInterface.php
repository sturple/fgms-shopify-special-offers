<?php

namespace Fgms\SpecialOffersBundle\Utility;

/**
 * An interface which may be implemented to provide
 * a channel through which to interact with the Shopify
 * API.
 */
interface ShopifyInterface
{
    /**
     * Invokes the Shopify REST API.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $args
     *
     * @return ShopifyObject
     */
    public function call($method, $endpoint, array $args = []);
}
