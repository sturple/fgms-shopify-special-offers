<?php

namespace Fgms\SpecialOffersBundle\Utility;

/**
 * Contains utilities for converting between
 * various data types.
 */
class Convert
{
    private static function raise($msg, $prev = null)
    {
        throw new \Fgms\SpecialOffersBundle\Exception\ConvertException($msg,0,$prev);
    }

    /**
     * Attempts to losslessly convert a string to
     * an integer.
     *
     * @param string $str
     *
     * @return int
     */
    public static function toInteger($str)
    {
        if (!preg_match('/^\\d+$/u',$str)) self::raise(
            sprintf(
                '"%s" cannot be losslessly converted to an integer',
                $str
            )
        );
        return intval($str);
    }

    private static function raiseCents($str, $prev = null)
    {
        self::raise(
            sprintf(
                '"%s" cannot be losslessly converted to cents',
                $str
            ),
            $prev
        );
    }

    /**
     * Attempts to losslessly convert a string
     * representing dollars to an integer giving
     * the number of cents.
     *
     * @param string $str
     *
     * @return int
     */
    public static function toCents($str)
    {
        $i = $str;
        if (preg_match('/^\\d+$/u',$i)) $i .= '.';
        if (preg_match('/\\.$/u',$i)) $i .= '00';
        if (preg_match('/\\.\\d$/u',$i)) $i .= '0';
        $i = preg_replace('/\\.(?=\\d{2}$)/u','',$i);
        try {
            return self::toInteger($i);
        } catch (\Fgms\SpecialOffersBundle\Exception\ConvertException $e) {
            self::raiseCents($str,$e);
        }
    }
}
