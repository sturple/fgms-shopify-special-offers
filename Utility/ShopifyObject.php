<?php

namespace Fgms\SpecialOffersBundle\Utility;

/**
 * Represents an object obtained from the Shopify API
 * providing a convenient way to interact with the otherwise
 * unsafe data.
 */
class ShopifyObject
{
    private $obj;
    private $json;
    private $path;

    private function __construct($obj, $json, $path = '')
    {
        $this->obj = $obj;
        $this->json = $json;
        $this->path = $path;
    }

    public static function create($json)
    {
        //  To guard against PSR-7 streams
        $json = (string)$json;
        try {
            $obj = Json::decode($json);
        } catch (\Fgms\SpecialOffersBundle\Exception\JsonException $e) {
            throw new \Fgms\SpecialOffersBundle\Exception\ShopifyException(
                sprintf(
                    'Error decoding Shopify JSON response: %s',
                    $json
                ),
                0,
                $e
            );
        }
        if (!is_object($obj)) throw new \Fgms\SpecialOffersBundle\Exception\ShopifyException(
            sprintf(
                'Root of Shopify JSON response is not object: %s',
                $json
            )
        );
        return new self($obj,$json);
    }

    public function __isset($key)
    {
        return isset($this->obj->$key);
    }

    public function __get($key)
    {
        if (!isset($this->obj->$key)) return null;
        return $this->obj->$key;
    }

    private function join($key)
    {
        //  TODO: JSON Pointer escaping
        return $this->path . '/' . $key;
    }

    private function getOptional($key, $type)
    {
        if (!isset($this->obj->$key)) return null;
        $v = $this->obj->$key;
        $func = 'is_' . $type;
        if (!call_user_func($func,$v)) throw new \Fgms\SpecialOffersBundle\Exception\ShopifyException(
            sprintf(
                'Expected %s to be %s: %s',
                $this->join($key),
                $type,
                $this->json
            )
        );
        return $v;
    }

    private function get($key, $type)
    {
        $v = $this->getOptional($key,$type);
        if (is_null($v)) throw new \Fgms\SpecialOffersBundle\Exception\ShopifyException(
            sprintf(
                'Expected %s: %s',
                $this->join($key),
                $this->json
            )
        );
        return $v;
    }

    /**
     * Obtains a property of the underlying object as an
     * optional string.
     *
     * @param string $key
     *
     * @return string|null
     *  Returns null if \em key is not present.
     */
    public function getOptionalString($key)
    {
        return $this->getOptional($key,'string');
    }

    /**
     * Obtains a property of the underlying object as a
     * string.
     *
     * @param string $key
     *
     * @return string
     */
    public function getString($key)
    {
        return $this->get($key,'string');
    }
}
