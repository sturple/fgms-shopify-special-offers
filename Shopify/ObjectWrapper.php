<?php

namespace Fgms\SpecialOffersBundle\Shopify;

/**
 * Represents an object obtained from the Shopify API
 * providing a convenient way to interact with the otherwise
 * unsafe data.
 */
class ObjectWrapper extends ValueWrapper
{
    private $obj;

    public function __construct($obj, $json, $path = '')
    {
        parent::__construct($json,$path);
        $this->obj = $obj;
    }

    public static function create($json)
    {
        //  To guard against PSR-7 streams
        $json = (string)$json;
        try {
            $obj = \Fgms\SpecialOffersBundle\Utility\Json::decode($json);
        } catch (\Fgms\SpecialOffersBundle\Exception\JsonException $e) {
            throw new Exception\DecodeException(
                sprintf(
                    'Error decoding Shopify JSON response: %s',
                    $json
                ),
                0,
                $e
            );
        }
        if (!is_object($obj)) throw new Exception\TypeMismatchException(
            'object',
            $obj,
            '',
            $json
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
    
    protected function get($key)
    {
        //  Use property_exists rather than isset
        //  so we get true for properties which
        //  exist but which are set to null
        if (!property_exists($this->obj,$key)) $this->raiseMissing($key);
        return $this->obj->$key;
    }

    protected function getOptional($key)
    {
        if (!isset($this->obj->$key)) return null;
        return $this->obj->$key;
    }
}
