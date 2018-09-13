<?php

namespace BCMathExtended;

/**
 * Class BC
 * @package BCMathExtended
 */
class BC
{
    const COMPARE_EQUAL = 0;
    const COMPARE_LEFT_GRATER = 1;
    const COMPARE_RIGHT_GRATER = -1;
    const DEFAULT_SCALE = 100;

    /**
     * @param null|int $scale
     */
    public static function setScale($scale)
    {
        bcscale($scale);
    }

    /**
     * @param int|string $number
     * @param int $precision
     * @return string
     */
    public static function round($number, $precision = 0)
    {
        $number = self::convertScientificNotationToString($number);
        if (self::checkIsFloat($number)) {
            if (self::isNegative($number)) {
                return self::sub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            }

            return self::add($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        return self::checkNumber($number);
    }

    /**
     * @param int|string|float $number
     * @return string
     */
    public static function convertScientificNotationToString($number)
    {
        // check if number is in scientific notation, first use stripos as is faster then preg_match
        if (false !== stripos($number, 'E') && preg_match('/(-?(\d+\.)?\d+)E([+-]?)(\d+)/i', $number, $regs)) {
            // calculate final scale of number
            $scale = $regs[4] + self::getDecimalsLengthFromNumber($regs[1]);
            $pow = self::pow(10, $regs[4], $scale);
            if ('-' === $regs[3]) {
                $number = self::div($regs[1], $pow, $scale);
            } else {
                $number = self::mul($pow, $regs[1], $scale);
            }
            // remove unnecessary 0 from 0.000 is a 0
            $number = rtrim($number, '0');
            // if you remove 0 you must clean dot
            $number = rtrim($number, '.');
        }

        return self::checkNumber($number);
    }

    /**
     * @param int|string|float $number
     * @return int
     */
    public static function getDecimalsLengthFromNumber($number)
    {
        $check = explode('.', $number);
        if (!empty($check[1])) {
            return strlen($check[1]);
        }

        return 0;
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return string
     */
    public static function pow($leftOperand, $rightOperand, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (self::checkIsFloat($rightOperand)) {
            if (null === $scale) {
                return self::powFractional($leftOperand, $rightOperand);
            }

            return self::powFractional($leftOperand, $rightOperand, $scale);
        }

        if (null === $scale) {
            return bcpow($leftOperand, $rightOperand);
        }

        return bcpow($leftOperand, $rightOperand, $scale);
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
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return string
     */
    private static function powFractional($leftOperand, $rightOperand, $scale = null)
    {
        // we need to increased scale to get correct results and avoid rounding error
        $increasedScale = null === $scale ? self::getScale() : $scale;
        $increasedScale *= 2;
        $decimals = explode('.', $rightOperand);

        return self::checkNumber(
            self::mul(
                self::exp(
                    self::mul(
                        self::log($leftOperand),
                        '0.' . $decimals[1],
                        $increasedScale
                    )
                ),
                self::pow($leftOperand, $decimals[0], $increasedScale),
                $scale
            )
        );
    }

    /**
     * @param int|string $number
     * @return int|string
     */
    private static function checkNumber($number)
    {
        $number = str_replace('+', '', filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
        if ('-0' === $number || !is_numeric($number)) {
            return '0';
        }

        return $number;
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return string
     */
    public static function mul($leftOperand, $rightOperand, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            return bcmul($leftOperand, $rightOperand);
        }

        return bcmul($leftOperand, $rightOperand, $scale);
    }

    /**
     * @param string $arg
     * @return string
     */
    public static function exp($arg)
    {
        $scale = self::DEFAULT_SCALE;
        $result = '1';
        for ($i = 299; $i > 0; $i--) {
            $result = self::add(self::mul(self::div($result, $i, $scale), $arg, $scale), 1, $scale);
        }

        return $result;
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return string
     */
    public static function add($leftOperand, $rightOperand, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            return bcadd($leftOperand, $rightOperand);
        }

        return bcadd($leftOperand, $rightOperand, $scale);
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return string
     */
    public static function div($leftOperand, $rightOperand, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            return bcdiv($leftOperand, $rightOperand);
        }

        return bcdiv($leftOperand, $rightOperand, $scale);
    }

    /**
     * @param string $arg
     * @return string
     */
    public static function log($arg)
    {
        $arg = self::convertScientificNotationToString($arg);
        if ($arg === '0') {
            return '-INF';
        }
        if (self::COMPARE_RIGHT_GRATER === self::comp($arg, '0')) {
            return 'NAN';
        }
        $scale = self::DEFAULT_SCALE;
        $m = (string)log($arg);
        $x = self::sub(self::div($arg, self::exp($m), $scale), '1', $scale);
        $res = '0';
        $pow = '1';
        $i = 1;
        do {
            $pow = self::mul($pow, $x, $scale);
            $sum = self::div($pow, $i, $scale);
            if ($i % 2 === 1) {
                $res = self::add($res, $sum, $scale);
            } else {
                $res = self::sub($res, $sum, $scale);
            }
            $i++;
        } while (self::comp($sum, '0', $scale));

        return self::add($res, $m, $scale);
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return int
     */
    public static function comp($leftOperand, $rightOperand, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            return bccomp($leftOperand, $rightOperand, max(strlen($leftOperand), strlen($rightOperand)));
        }

        return bccomp(
            $leftOperand,
            $rightOperand,
            $scale
        );
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param null|int $scale
     * @return string
     */
    public static function sub($leftOperand, $rightOperand, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            return bcsub($leftOperand, $rightOperand);
        }

        return bcsub($leftOperand, $rightOperand, $scale);
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
     * @return string
     */
    public static function abs($number)
    {
        $number = self::convertScientificNotationToString($number);

        if (self::isNegative($number)) {
            $number = (string)substr($number, 1);
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
        $max = self::convertScientificNotationToString($max);
        $min = self::convertScientificNotationToString($min);

        $difference = self::add(self::sub($max, $min), 1);
        $randPercent = self::div(mt_rand(), mt_getrandmax(), 8);

        return self::add($min, self::mul($difference, $randPercent, 8), 0);
    }

    /**
     * @param array|int|string,...
     * @return null|string
     */
    public static function max()
    {
        $max = null;
        $args = func_get_args();
        if (is_array($args[0])) {
            $args = $args[0];
        }
        foreach ($args as $number) {
            $number = self::convertScientificNotationToString($number);
            if (null === $max) {
                $max = $number;
            } else if (self::comp($max, $number) === self::COMPARE_RIGHT_GRATER) {
                $max = $number;
            }
        }

        return $max;
    }

    /**
     * @param array|int|string,...
     * @return null|string
     */
    public static function min()
    {
        $min = null;
        $args = func_get_args();
        if (is_array($args[0])) {
            $args = $args[0];
        }
        foreach ($args as $number) {
            $number = self::convertScientificNotationToString($number);
            if (null === $min) {
                $min = $number;
            } else if (self::comp($min, $number) === self::COMPARE_LEFT_GRATER) {
                $min = $number;
            }
        }

        return $min;
    }

    /**
     * @param int|string $number
     * @param int $precision
     * @return string
     */
    public static function roundDown($number, $precision = 0)
    {
        $number = self::convertScientificNotationToString($number);
        $multiply = self::pow(10, (string)abs($precision));

        return $precision < 0 ?
            self::mul(
                self::floor(self::div($number, $multiply, self::getDecimalsLengthFromNumber($number))), $multiply,
                $precision
            ) :
            self::div(
                self::floor(self::mul($number, $multiply, self::getDecimalsLengthFromNumber($number))), $multiply,
                $precision
            );
    }

    /**
     * @param int|string $number
     * @return string
     */
    public static function floor($number)
    {
        $number = self::convertScientificNotationToString($number);
        if (self::checkIsFloat($number) && self::checkIsFloatCleanZeros($number)) {
            $result = 0;
            if (self::isNegative($number)) {
                --$result;
            }
            $number = self::add($number, $result, 0);
        }

        return self::checkNumber($number);
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
     * @param int|string $number
     * @param int $precision
     * @return string
     */
    public static function roundUp($number, $precision = 0)
    {
        $number = self::convertScientificNotationToString($number);
        $multiply = self::pow(10, (string)abs($precision));

        return $precision < 0 ?
            self::mul(
                self::ceil(self::div($number, $multiply, self::getDecimalsLengthFromNumber($number))), $multiply,
                $precision
            ) :
            self::div(
                self::ceil(self::mul($number, $multiply, self::getDecimalsLengthFromNumber($number))), $multiply,
                $precision
            );
    }

    /**
     * @param int|string $number
     * @return string
     */
    public static function ceil($number)
    {
        $number = self::convertScientificNotationToString($number);
        if (self::checkIsFloat($number) && self::checkIsFloatCleanZeros($number)) {
            $result = 1;
            if (self::isNegative($number)) {
                --$result;
            }
            $number = self::add($number, $result, 0);
        }

        return self::checkNumber($number);
    }

    /**
     * @return int
     */
    public static function getScale()
    {
        $sqrt = self::sqrt('2');

        return strlen(substr($sqrt, strpos($sqrt, '.') + 1));
    }

    /**
     * @param string $operand
     * @param null|int $scale
     * @return string
     */
    public static function sqrt($operand, $scale = null)
    {
        $operand = self::convertScientificNotationToString($operand);

        if (null === $scale) {
            return bcsqrt($operand);
        }

        return bcsqrt($operand, $scale);
    }

    /**
     * @param string $leftOperand
     * @param string $modulus
     * @param null $scale
     * @return string
     */
    public static function mod($leftOperand, $modulus, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);

        // bcmod in 7.2 is not working properly - for example bcmod(9.9999E-10, -0.00056, 9) should return '-0.000559999' but returns 0.0000000

        // bcmod in php 5.6< don't support scale and floats
        // let use this $x - floor($x/$y) * $y;
        if (null === $scale) {
            return self::sub($leftOperand, self::mul(self::floor(self::div($leftOperand, $modulus)), $modulus));
        }

        return self::sub(
            $leftOperand, self::mul(self::floor(self::div($leftOperand, $modulus, $scale)), $modulus, $scale), $scale
        );
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param string $modulus
     * @param null|int $scale
     * @return string
     */
    public static function powMod($leftOperand, $rightOperand, $modulus, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        // bcpowmod in 5.6 have don't calculate correct results if scale is empty
        if (null === $scale) {
            return self::mod(self::pow($leftOperand, $rightOperand), $modulus);
        }

        // cant use bcpowmod here as it don't support floats
        if (self::checkIsFloat($leftOperand) || self::checkIsFloat($rightOperand) || self::checkIsFloat($modulus)) {
            return self::mod(self::pow($leftOperand, $rightOperand, $scale), $modulus, $scale);
        }

        return bcpowmod($leftOperand, $rightOperand, $modulus, $scale);
    }

    /**
     * @param string $arg
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function fact($arg)
    {
        $arg = self::convertScientificNotationToString($arg);

        if (self::checkIsFloat($arg)) {
            throw new \InvalidArgumentException('Number has to be an integer');
        }
        if (self::isNegative($arg)) {
            throw new \InvalidArgumentException('Number has to be greater than or equal to 0');
        }

        $return = '1';
        for ($i = 2; $i <= $arg; ++$i) {
            $return = self::mul($return, $i);
        }

        return $return;
    }

    /**
     * @param string $hex
     * @return string
     */
    public static function hexdec($hex) {
        $remainingDigits = substr($hex, 0, -1);
        $lastDigitToDecimal = \hexdec(substr($hex, -1));

        if (strlen($remainingDigits) === 0) {
            return $lastDigitToDecimal;
        }

        return self::add(self::mul(16, self::hexdec($remainingDigits)), $lastDigitToDecimal, 0);
    }

    /**
     * @param int $decimal
     * @return string
     */
    public static function dechex($decimal) {
        $quotient = self::div($decimal, 16, 0);
        $remainderToHex = \dechex(self::mod($decimal, 16));

        if (self::comp($quotient, 0) === 0) {
            return $remainderToHex;
        }

        return self::dechex($quotient) . $remainderToHex;
    }
}
