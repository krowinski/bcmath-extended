<?php

namespace BCMathExtended;

/**
 * Class BC
 * @package BCMathExtended
 */
class BC
{
    /**
     * @param null|int $scale
     */
    public static function setScale($scale)
    {
        bcscale($scale);
    }

    /**
     * @param int|string $number
     * @return string
     */
    public static function ceil($number)
    {
        $number = (string)$number;

        if (true === self::checkIsFloat($number) and true === self::checkIsFloatCleanZeros($number))
        {
            $result = 1;
            if (true === self::isNegative($number))
            {
                --$result;
            }
            $number = bcadd($number, $result, 0);
        }

        return self::checkNumber($number);
    }

    /**
     * @param int|string $number
     * @return bool
     */
    private static function checkIsFloat($number)
    {
        return false !== strpos($number, '.');
    }

    /**
     * @param int|string $number
     * @return bool
     */
    private static function checkIsFloatCleanZeros(&$number)
    {
        return false !== strpos($number = rtrim(rtrim($number, '0'), '.'), '.');
    }

    /**
     * @param $number
     * @return bool
     */
    private static function isNegative($number)
    {
        return 0 === strncmp('-', $number, 1);
    }

    /**
     * @param int|string $number
     * @return int|string
     */
    private static function checkNumber($number)
    {
        $number = str_replace('+', '', filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
        if ('' === $number || '-0' === $number)
        {
            return '0';
        }
        return $number;
    }

    /**
     * @param int|string $number
     * @param int $precision
     * @return string
     */
    public static function round($number, $precision = 0)
    {
        $number = (string)$number;

        if (true === self::checkIsFloat($number))
        {
            if (true === self::isNegative($number))
            {
                return bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            }

            return bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        return self::checkNumber($number);
    }

    /**
     * @param int|string $number
     * @return string
     */
    public static function floor($number)
    {
        $number = (string)$number;

        if (true === self::checkIsFloat($number) and true === self::checkIsFloatCleanZeros($number))
        {
            $result = 0;
            if (true === self::isNegative($number))
            {
                --$result;
            }
            $number = bcadd($number, $result, 0);
        }

        return self::checkNumber($number);
    }

    /**
     * @param int|string $number
     * @return string
     */
    public static function abs($number)
    {
        $number = (string)$number;

        if (true === self::isNegative($number))
        {
            $number = substr($number, 1);
        }

        return self::checkNumber($number);
    }

    /**
     * @param int|string $min
     * @param int|string $max
     * @return string
     */
    public static function rand($min, $max)
    {
        $max = (string)$max;
        $min = (string)$min;

        $difference = bcadd(bcsub($max, $min), 1);
        $rand_percent = bcdiv(mt_rand(), mt_getrandmax(), 8);

        return bcadd($min, bcmul($difference, $rand_percent, 8), 0);
    }

    /**
     * @param int|string,...
     * @return null|int|string
     */
    public static function max()
    {
        $max = null;
        foreach (func_get_args() as $value)
        {
            if (null === $max)
            {
                $max = $value;
            }
            else
            {
                if (bccomp($max, $value) < 0)
                {
                    $max = $value;
                }
            }
        }

        return $max;
    }

    /**
     * @param int|string,...
     * @return null|int|string
     */
    public static function min()
    {
        $min = null;
        foreach (func_get_args() as $value)
        {
            if (null === $min)
            {
                $min = $value;
            }
            else
            {
                if (bccomp($min, $value) > 0)
                {
                    $min = $value;
                }
            }
        }

        return $min;
    }
}