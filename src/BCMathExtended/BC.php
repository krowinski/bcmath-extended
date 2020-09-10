<?php

declare(strict_types=1);

namespace BCMathExtended;

use Closure;
use InvalidArgumentException;

class BC
{
    public const COMPARE_EQUAL = 0;
    public const COMPARE_LEFT_GRATER = 1;
    public const COMPARE_RIGHT_GRATER = -1;

    protected const DEFAULT_SCALE = 100;

    protected const MAX_BASE = 256;

    protected const BIT_OPERATOR_AND = 'and';
    protected const BIT_OPERATOR_OR = 'or';
    protected const BIT_OPERATOR_XOR = 'xor';

    protected static $trimTrailingZeroes = true;

    public static function rand(string $min, string $max): string
    {
        $max = static::convertScientificNotationToString($max);
        $min = static::convertScientificNotationToString($min);

        $difference = static::add(static::sub($max, $min), '1');
        $randPercent = static::div((string)mt_rand(), (string)mt_getrandmax(), 8);

        return static::add($min, static::mul($difference, $randPercent, 8), 0);
    }

    public static function convertScientificNotationToString(string $number): string
    {
        // check if number is in scientific notation, first use stripos as is faster then preg_match
        if (false !== stripos($number, 'E') && preg_match('/(-?(\d+\.)?\d+)E([+-]?)(\d+)/i', $number, $regs)) {
            // calculate final scale of number
            $scale = $regs[4] + static::getDecimalsLengthFromNumber($regs[1]);
            $pow = static::pow('10', $regs[4], $scale);
            if ('-' === $regs[3]) {
                $number = static::div($regs[1], $pow, $scale);
            } else {
                $number = static::mul($pow, $regs[1], $scale);
            }
            // remove unnecessary 0 and dot from 0.000 is a 0
            $number = static::formatTrailingZeroes($number, $scale);
        }

        return static::checkNumber($number);
    }

    public static function getDecimalsLengthFromNumber(string $number): int
    {
        $check = explode('.', $number);
        if (!empty($check[1])) {
            return strlen($check[1]);
        }

        return 0;
    }

    public static function pow(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (static::checkIsFloat($rightOperand)) {
            if (null === $scale) {
                $r = static::powFractional($leftOperand, $rightOperand);
            } else {
                $r = static::powFractional($leftOperand, $rightOperand, $scale);
            }
        } elseif (null === $scale) {
            $r = bcpow($leftOperand, $rightOperand);
        } else {
            $r = bcpow($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    protected static function checkIsFloat(string $number): bool
    {
        return false !== strpos($number, '.');
    }

    protected static function powFractional(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        // we need to increased scale to get correct results and avoid rounding error
        $currentScale = $scale ?? static::getScale();
        $increasedScale = $currentScale * 2;

        // add zero to trim scale
        return static::checkNumber(
            static::add(
                static::exp(static::mul($rightOperand, static::log($leftOperand), $increasedScale)),
                '0',
                $currentScale
            )
        );
    }

    public static function getScale(): int
    {
        if (PHP_VERSION_ID >= 70300) {
            /** @noinspection PhpStrictTypeCheckingInspection */
            /** @noinspection PhpParamsInspection */
            return bcscale();
        }

        $sqrt = static::sqrt('2');

        return strlen(substr($sqrt, strpos($sqrt, '.') + 1));
    }

    public static function sqrt(string $operand, ?int $scale = null): string
    {
        $operand = static::convertScientificNotationToString($operand);

        if (null === $scale) {
            $r = bcsqrt($operand);
        } else {
            $r = bcsqrt($operand, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    protected static function formatTrailingZeroes(string $number, ?int $scale = null): string
    {
        if (self::$trimTrailingZeroes) {
            return static::trimTrailingZeroes($number);
        }

        // newer version of php correct add trailing zeros
        if (PHP_VERSION_ID >= 70300) {
            return $number;
        }

        // old one not so much..
        return self::addTrailingZeroes($number, $scale);
    }

    protected static function trimTrailingZeroes(string $number): string
    {
        if (false !== strpos($number, '.')) {
            $number = rtrim($number, '0');
        }

        return rtrim($number, '.') ?: '0';
    }

    protected static function addTrailingZeroes(string $number, ?int $scale): string
    {
        if (null === $scale) {
            return $number;
        }

        $decimalLength = static::getDecimalsLengthFromNumber($number);
        if ($scale === $decimalLength) {
            return $number;
        }

        if (0 === $decimalLength) {
            $number .= '.';
        }

        return str_pad($number, strlen($number) + ($scale - $decimalLength), '0', STR_PAD_RIGHT);
    }

    protected static function checkNumber(string $number): string
    {
        $number = str_replace('+', '', filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
        if ('-0' === $number || !is_numeric($number)) {
            return '0';
        }

        return $number;
    }

    public static function add(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            $r = bcadd($leftOperand, $rightOperand);
        } else {
            $r = bcadd($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    public static function exp(string $arg): string
    {
        $scale = static::DEFAULT_SCALE;
        $result = '1';
        for ($i = 299; $i > 0; --$i) {
            $result = static::add(static::mul(static::div($result, (string)$i, $scale), $arg, $scale), '1', $scale);
        }

        return $result;
    }

    public static function mul(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            $r = bcmul($leftOperand, $rightOperand);
        } else {
            $r = bcmul($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    public static function div(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if ('0' === static::trimTrailingZeroes($rightOperand)) {
            throw new InvalidArgumentException('Division by zero');
        }

        if (null === $scale) {
            $r = bcdiv($leftOperand, $rightOperand);
        } else {
            $r = bcdiv($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    public static function log(string $arg): string
    {
        $arg = static::convertScientificNotationToString($arg);
        if ($arg === '0') {
            return '-INF';
        }
        if (static::COMPARE_RIGHT_GRATER === static::comp($arg, '0')) {
            return 'NAN';
        }
        $scale = static::DEFAULT_SCALE;
        $m = (string)log((float)$arg);
        $x = static::sub(static::div($arg, static::exp($m), $scale), '1', $scale);
        $res = '0';
        $pow = '1';
        $i = 1;
        do {
            $pow = static::mul($pow, $x, $scale);
            $sum = static::div($pow, (string)$i, $scale);
            if ($i % 2 === 1) {
                $res = static::add($res, $sum, $scale);
            } else {
                $res = static::sub($res, $sum, $scale);
            }
            ++$i;
        } while (static::comp($sum, '0', $scale));

        return static::add($res, $m, $scale);
    }

    public static function comp(string $leftOperand, string $rightOperand, ?int $scale = null): int
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            return bccomp($leftOperand, $rightOperand, max(strlen($leftOperand), strlen($rightOperand)));
        }

        return bccomp(
            $leftOperand,
            $rightOperand,
            $scale
        );
    }

    public static function sub(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            $r = bcsub($leftOperand, $rightOperand);
        } else {
            $r = bcsub($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    public static function setTrimTrailingZeroes(bool $flag): void
    {
        self::$trimTrailingZeroes = $flag;
    }

    public static function max(...$ags): ?string
    {
        $max = null;
        foreach (static::parseArgs($ags) as $number) {
            $number = static::convertScientificNotationToString((string)$number);
            if (null === $max) {
                $max = $number;
            } elseif (static::comp($max, $number) === static::COMPARE_RIGHT_GRATER) {
                $max = $number;
            }
        }

        return $max;
    }

    protected static function parseArgs(array $args): array
    {
        if (is_array($args[0])) {
            $args = $args[0];
        }

        return $args;
    }

    public static function min(...$ags): ?string
    {
        $min = null;
        foreach (static::parseArgs($ags) as $number) {
            $number = static::convertScientificNotationToString((string)$number);
            if (null === $min) {
                $min = $number;
            } elseif (static::comp($min, $number) === static::COMPARE_LEFT_GRATER) {
                $min = $number;
            }
        }

        return $min;
    }

    public static function powMod(
        string $leftOperand,
        string $rightOperand,
        string $modulus,
        ?int $scale = null
    ): string {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        // bcpowmod in 5.6 have don't calculate correct results if scale is empty
        if (null === $scale) {
            $r = static::mod(static::pow($leftOperand, $rightOperand), $modulus);
        } elseif (static::checkIsFloat($leftOperand) || static::checkIsFloat($rightOperand) || static::checkIsFloat(
                $modulus
            )) {
            // cant use bcpowmod here as it don't support floats
            $r = static::mod(static::pow($leftOperand, $rightOperand, $scale), $modulus, $scale);
        } else {
            $r = bcpowmod($leftOperand, $rightOperand, $modulus, $scale);
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    public static function mod(string $leftOperand, string $modulus, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);

        // bcmod in 7.2 is not working properly - for example bcmod(9.9999E-10, -0.00056, 9) should return '-0.000559999' but returns 0.0000000

        // bcmod in php 5.6< don't support scale and floats
        // let use this $x - floor($x/$y) * $y;
        if (null === $scale) {
            $r = static::sub(
                $leftOperand,
                static::mul(static::floor(static::div($leftOperand, $modulus)), $modulus)
            );
        } else {
            $r = static::sub(
                $leftOperand,
                static::mul(static::floor(static::div($leftOperand, $modulus, $scale)), $modulus, $scale),
                $scale
            );
        }

        return static::formatTrailingZeroes($r, $scale);
    }

    public static function floor(string $number): string
    {
        $number = static::convertScientificNotationToString($number);
        if (static::checkIsFloat($number) && static::checkIsFloatCleanZeros($number)) {
            $result = 0;
            if (static::isNegative($number)) {
                --$result;
            }
            $number = static::add($number, (string)$result, 0);
        }

        return static::checkNumber($number);
    }

    protected static function checkIsFloatCleanZeros(string &$number): bool
    {
        return false !== strpos($number = static::trimTrailingZeroes($number), '.');
    }

    protected static function isNegative(string $number): bool
    {
        return 0 === strncmp('-', $number, 1);
    }

    public static function fact(string $arg): string
    {
        $arg = static::convertScientificNotationToString($arg);

        if (static::checkIsFloat($arg)) {
            throw new InvalidArgumentException('Number has to be an integer');
        }
        if (static::isNegative($arg)) {
            throw new InvalidArgumentException('Number has to be greater than or equal to 0');
        }

        $return = '1';
        for ($i = 2; $i <= $arg; ++$i) {
            $return = static::mul($return, (string)$i);
        }

        return $return;
    }

    public static function hexdec(string $hex): string
    {
        $remainingDigits = substr($hex, 0, -1);
        $lastDigitToDecimal = (string)hexdec(substr($hex, -1));

        if ('' === $remainingDigits) {
            return $lastDigitToDecimal;
        }

        return static::add(static::mul('16', static::hexdec($remainingDigits)), $lastDigitToDecimal, 0);
    }

    public static function dechex(string $decimal): string
    {
        $quotient = static::div($decimal, '16', 0);
        $remainderToHex = dechex((int)static::mod($decimal, '16'));

        if (static::comp($quotient, '0') === static::COMPARE_EQUAL) {
            return $remainderToHex;
        }

        return static::dechex($quotient) . $remainderToHex;
    }

    public static function bitAnd(
        string $leftOperand,
        string $rightOperand
    ): string {
        return static::bitOperatorHelper($leftOperand, $rightOperand, static::BIT_OPERATOR_AND);
    }

    protected static function bitOperatorHelper(string $leftOperand, string $rightOperand, string $operator): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if (static::checkIsFloat($leftOperand)) {
            throw new InvalidArgumentException('Left operator has to be an integer');
        }
        if (static::checkIsFloat($rightOperand)) {
            throw new InvalidArgumentException('Right operator has to be an integer');
        }

        $leftOperandNegative = static::isNegative($leftOperand);
        $rightOperandNegative = static::isNegative($rightOperand);

        $leftOperand = static::dec2bin(static::abs($leftOperand));
        $rightOperand = static::dec2bin(static::abs($rightOperand));

        $maxLength = max(strlen($leftOperand), strlen($rightOperand));

        $leftOperand = static::alignBinLength($leftOperand, $maxLength);
        $rightOperand = static::alignBinLength($rightOperand, $maxLength);

        if ($leftOperandNegative) {
            $leftOperand = static::recalculateNegative($leftOperand);
        }
        if ($rightOperandNegative) {
            $rightOperand = static::recalculateNegative($rightOperand);
        }

        $isNegative = false;
        $result = '';
        if (static::BIT_OPERATOR_AND === $operator) {
            $result = $leftOperand & $rightOperand;
            $isNegative = ($leftOperandNegative and $rightOperandNegative);
        } elseif (static::BIT_OPERATOR_OR === $operator) {
            $result = $leftOperand | $rightOperand;
            $isNegative = ($leftOperandNegative or $rightOperandNegative);
        } elseif (static::BIT_OPERATOR_XOR === $operator) {
            $result = $leftOperand ^ $rightOperand;
            $isNegative = ($leftOperandNegative xor $rightOperandNegative);
        }

        if ($isNegative) {
            $result = static::recalculateNegative($result);
        }

        $result = static::bin2dec($result);

        return $isNegative ? '-' . $result : $result;
    }

    public static function dec2bin(string $number, int $base = self::MAX_BASE): string
    {
        return static::decBaseHelper(
            $base,
            static function (int $base) use ($number) {
                $value = '';
                if ('0' === $number) {
                    return chr((int)$number);
                }

                while (BC::comp($number, '0') !== BC::COMPARE_EQUAL) {
                    $rest = BC::mod($number, (string)$base);
                    $number = BC::div($number, (string)$base);
                    $value = chr((int)$rest) . $value;
                }

                return $value;
            }
        );
    }

    protected static function decBaseHelper(int $base, Closure $closure): string
    {
        if ($base < 2 || $base > static::MAX_BASE) {
            throw new InvalidArgumentException('Invalid Base: ' . $base);
        }
        $orgScale = static::getScale();
        static::setScale(0);

        $value = $closure($base);

        static::setScale($orgScale);

        return $value;
    }

    public static function setScale(int $scale): void
    {
        bcscale($scale);
    }

    public static function abs(string $number): string
    {
        $number = static::convertScientificNotationToString($number);

        if (static::isNegative($number)) {
            $number = (string)substr($number, 1);
        }

        return static::checkNumber($number);
    }

    protected static function alignBinLength(string $string, int $length): string
    {
        return str_pad($string, $length, static::dec2bin('0'), STR_PAD_LEFT);
    }

    protected static function recalculateNegative(string $number): string
    {
        $xor = str_repeat(static::dec2bin((string)(static::MAX_BASE - 1)), strlen($number));
        $number ^= $xor;
        for ($i = strlen($number) - 1; $i >= 0; --$i) {
            $byte = ord($number[$i]);
            if (++$byte !== static::MAX_BASE) {
                $number[$i] = chr($byte);
                break;
            }
        }

        return $number;
    }

    public static function bin2dec(string $binary, int $base = self::MAX_BASE): string
    {
        return static::decBaseHelper(
            $base,
            static function (int $base) use ($binary) {
                $size = strlen($binary);
                $return = '0';
                for ($i = 0; $i < $size; ++$i) {
                    $element = ord($binary[$i]);
                    $power = BC::pow((string)$base, (string)($size - $i - 1));
                    $return = BC::add($return, BC::mul((string)$element, $power));
                }

                return $return;
            }
        );
    }

    public static function bitOr(string $leftOperand, string $rightOperand): string
    {
        return static::bitOperatorHelper($leftOperand, $rightOperand, static::BIT_OPERATOR_OR);
    }

    public static function bitXor(string $leftOperand, string $rightOperand): string
    {
        return static::bitOperatorHelper($leftOperand, $rightOperand, static::BIT_OPERATOR_XOR);
    }

    public static function roundHalfEven(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        if (!static::checkIsFloat($number)) {
            return static::checkNumber($number);
        }

        $precessionPos = strpos($number, '.') + $precision + 1;
        if (strlen($number) <= $precessionPos) {
            return static::round($number, $precision);
        }

        if ($number[$precessionPos] !== '5') {
            return static::round($number, $precision);
        }

        $isPrevEven = $number[$precessionPos - 1] === '.'
            ? (int)$number[$precessionPos - 2] % 2 === 0
            : (int)$number[$precessionPos - 1] % 2 === 0;
        $isNegative = static::isNegative($number);

        if ($isPrevEven === $isNegative) {
            return static::roundUp($number, $precision);
        }

        return static::roundDown($number, $precision);
    }

    public static function round(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        if (static::checkIsFloat($number)) {
            if (static::isNegative($number)) {
                return static::sub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            }

            return static::add($number, '0.' . str_repeat('0', $precision) . '5', $precision);
        }

        return static::checkNumber($number);
    }

    public static function roundUp(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        $multiply = static::pow('10', (string)abs($precision));

        return $precision < 0
            ?
            static::mul(
                static::ceil(static::div($number, $multiply, static::getDecimalsLengthFromNumber($number))),
                $multiply,
                $precision
            )
            :
            static::div(
                static::ceil(static::mul($number, $multiply, static::getDecimalsLengthFromNumber($number))),
                $multiply,
                $precision
            );
    }

    public static function ceil(string $number): string
    {
        $number = static::convertScientificNotationToString($number);
        if (static::checkIsFloat($number) && static::checkIsFloatCleanZeros($number)) {
            $result = 1;
            if (static::isNegative($number)) {
                --$result;
            }
            $number = static::add($number, (string)$result, 0);
        }

        return static::checkNumber($number);
    }

    public static function roundDown(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        $multiply = static::pow('10', (string)abs($precision));

        return $precision < 0
            ?
            static::mul(
                static::floor(static::div($number, $multiply, static::getDecimalsLengthFromNumber($number))),
                $multiply,
                $precision
            )
            :
            static::div(
                static::floor(static::mul($number, $multiply, static::getDecimalsLengthFromNumber($number))),
                $multiply,
                $precision
            );
    }
}
