<?php

namespace Fgms\SpecialOffersBundle\Utility;

/**
 * Contains static methods for working with
 * JSON data.
 */
class Json
{
    private static function check()
    {
        $code = json_last_error();
        if ($code === JSON_ERROR_NONE) return;
        throw new \Fgms\SpecialOffersBundle\Exception\JsonException(
            json_last_error_msg(),
            $code
        );
    }

    public static function encode($value, $options = 0, $depth = 512)
    {
        $retr = json_encode($value,$options,$depth);
        self::check();
        return $retr;
    }

    public static function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $retr = json_decode($json,$assoc,$depth,$options);
        self::check();
        return $retr;
    }

    public static function decodeArray($json, $assoc = false, $depth = 512, $options = 0)
    {
        $retr = self::decode($json,$assoc,$depth,$options);
        if (!is_array($retr)) throw new \Fgms\SpecialOffersBundle\Exception\JsonException(
            'Expected JSON array'
        );
        return $retr;
    }

    public static function decodeIntegerArray($json, $options = 0)
    {
        $retr = self::decodeArray($json,false,2,$options);
        foreach ($retr as $i) if (!is_int($i)) throw new \Fgms\SpecialOffersBundle\Exception\JsonException(
            'Expected JSON array of integers'
        );
        return $retr;
    }

    public static function decodeStringArray($json, $options = 0)
    {
        $retr = self::decodeArray($json,false,2,$options);
        foreach ($retr as $s) if (!is_string($s)) throw new \Fgms\SpecialOffersBundle\Exception\JsonException(
            'Expected JSON array of strings'
        );
        return $retr;
    }

    public static function decodeObjectArray($json, $depth = 512, $options = 0)
    {
        $retr = self::decodeArray($json,false,$depth,$options);
        foreach ($retr as $o) if (!is_object($o)) throw new \Fgms\SpecialOffersBundle\Exception\JsonException(
            'Expected JSON array of objects'
        );
        return $retr;
    }
}
