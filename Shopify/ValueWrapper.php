<?php

namespace Fgms\SpecialOffersBundle\Shopify;

/**
 * A base class for safe wrappers for arrays and
 * objects returned from the Shopify API.
 */
abstract class ValueWrapper
{
    private $json;
    private $path;

    public function __construct($json, $path)
    {
        $this->json = $json;
        $this->path = $path;
    }

    private function join($key)
    {
        //  TODO: JSON Pointer escaping
        return $this->path . '/' . $key;
    }

    /**
     * May be called from a derived class to raise
     * an exception indicating that a certain string
     * or integer key is not present.
     *
     * @param string|int $key
     */
    protected function raiseMissing($key)
    {
        throw new Exception\MissingException(
            $this->join($key),
            $this->json
        );
    }

    /**
     * Retrieves the value associated with a key.
     *
     * @param string|int $key
     *
     * @return mixed
     */
    protected abstract function get($key);

    /**
     * Retrieves the value associated with a key, or
     * null if there is no such value.
     *
     * @param string|int $key
     *
     * @return mixed|null
     */
    protected abstract function getOptional($key);

    /**
     * Wraps a value in an ObjectWrapper or ArrayWrapper,
     * or returns it unmodified as appropriate.
     *
     * @param string|int $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function wrap($key, $value)
    {
        if (is_object($value)) return new ObjectWrapper($value,$this->json,$this->join($key));
        if (is_array($value)) return new ArrayWrapper($value,$this->json,$this->join($key));
        return $value;
    }

    /**
     * Allows getType methods to be invoked on object's
     * of this type, where Type is string, integer,
     * float/double, bool/boolean, object, array, or null.
     *
     * Additionally getOptionalType methods may be invoked
     * which may return null.
     *
     * These methods all accept exactly one string or
     * integer argument: The key whole value shall be
     * retrieved.
     */
    public function __call($name, array $arguments)
    {
        if (
            (count($arguments) !== 1) ||
            !(
                is_string($arguments[0]) ||
                is_integer($arguments[0])
            )
        ) throw new \BadMethodCallException(
            'get[Optional]<type> accepts exactly one string or integer argument'
        );
        $str = preg_replace('/^get/u','',$name,-1,$count);
        if ($count === 0) throw new \BadMethodCallException(
            sprintf('"%s" is not a valid get[Optional]<type> method',$name)
        );
        $str = preg_replace('/^Optional/u','',$str,-1,$count);
        $opt = $count !== 0;
        $type = strtolower($str);
        //  There is no is_boolean
        if ($type === 'boolean') $type = 'bool';
        $key = $arguments[0];
        if ($opt) {
            $val = $this->getOptional($key);
            if (is_null($val)) return null;
        } else {
            $val = $this->get($key);
        }
        $func = 'is_' . $type;
        if (!is_callable($func)) throw new \BadMethodCallException(
            sprintf(
                '"%s" is not a recognized type',
                $type
            )
        );
        if (!call_user_func($func,$val)) throw new Exception\TypeMismatchException(
            $type,
            $val,
            $this->join($key),
            $this->json
        );
        return $this->wrap($key,$val);
    }
}
