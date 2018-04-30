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

    /**
     * @param int $scale
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
    private static function getDecimalsLengthFromNumber($number)
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
     * @param int $scale
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
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int $scale
     * @return string
     */
    public static function pow($leftOperand, $rightOperand, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            return bcpow($leftOperand, $rightOperand);
        }

        return bcpow($leftOperand, $rightOperand, $scale);
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int $scale
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
     * @param int|string $number
     * @return bool
     */
    private static function checkIsFloat($number)
    {
        return false !== strpos($number, '.');
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
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int $scale
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
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int $scale
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
     * @param string $leftOperand
     * @param string $rightOperand
     * @param int $scale
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
     * @param int $scale
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
     * @param int $scale
     * @return string
     */
    public static function fmod($leftOperand, $modulus, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);

        // From PHP 7.2 on, bcmod can handle real numbers
        if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
            if (null === $scale) {
                return bcmod($leftOperand, $modulus);
            }
            return bcmod($leftOperand, $modulus, $scale);
        }

        // mod(a, b) = a - b * floor(a/b)
        return self::sub(
            $leftOperand,
            self::mul(
                $modulus,
                self::floor(self::div($leftOperand, $modulus, $scale)),
                $scale
            ),
            $scale
        );
    }

    /**
     * @param string $leftOperand
     * @param string $modulus
     * @return string
     */
    public static function mod($leftOperand, $modulus)
    {
        return bcmod(
            self::convertScientificNotationToString($leftOperand),
            $modulus
        );
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param string $modulus
     * @param int $scale
     * @return string
     */
    public static function powMod($leftOperand, $rightOperand, $modulus, $scale = null)
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            return bcpowmod($leftOperand, $rightOperand, $modulus);
        }

        return bcpowmod($leftOperand, $rightOperand, $modulus, $scale);
    }
}
