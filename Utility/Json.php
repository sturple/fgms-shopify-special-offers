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
}
