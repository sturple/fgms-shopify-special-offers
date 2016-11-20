<?php

namespace Fgms\SpecialOffersBundle\Shopify;

/**
 * Represents an array obtained from the Shopify API
 * providing a convenient way to interact with the
 * otherwise unsafe data.
 */
class ArrayWrapper extends ValueWrapper implements \ArrayAccess, \IteratorAggregate, \Countable
{
    private $arr;

    public function __construct(array $arr, $json, $path)
    {
        parent::__construct($json,$path);
        $this->arr = $arr;
    }

    public function count()
    {
        return count($this->arr);
    }

    public function getIterator()
    {
        foreach ($this->arr as $key => $value) yield $key => $this->wrap($key,$value);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset,$this->arr);
    }

    public function offsetGet($offset)
    {
        return $this->wrap($offset,$this->arr[$offset]);
    }

    private function raiseImmutable()
    {
        throw new \LogicException('Shopify API arrays are immutable');
    }

    public function offsetSet($offset, $value)
    {
        $this->raiseImmutable();
    }

    public function offsetUnset($offset)
    {
        $this->raiseImmutable();
    }

    protected function get($key)
    {
        if (!$this->offsetExists($key)) $this->raiseMissing($key);
        return $this->arr[$key];
    }

    protected function getOptional($key)
    {
        if (!$this->offsetExists($key)) return null;
        return $this->arr[$key];
    }
}
