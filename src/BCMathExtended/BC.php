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

    protected static bool $trimTrailingZeroes = true;

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
        // check if number is in scientific notation, first use stripos as is faster than preg_match
        if (stripos($number, 'E') !== false && preg_match('/(-?(\d+\.)?\d+)E([+-]?)(\d+)/i', $number, $regs)) {
            // calculate final scale of number
            $scale = (int)$regs[4] + static::getDecimalsLength($regs[1]);
            $pow = static::pow('10', $regs[4], $scale);
            if ($regs[3] === '-') {
                $number = static::div($regs[1], $pow, $scale);
            } else {
                $number = static::mul($pow, $regs[1], $scale);
            }
            $number = static::formatTrailingZeroes($number);
        }

        return static::parseNumber($number);
    }

    public static function getDecimalsLength(string $number): int
    {
        if (static::isFloat($number)) {
            return strcspn(strrev($number), '.');
        }

        return 0;
    }

    protected static function isFloat(string $number): bool
    {
        return strpos($number, '.') !== false;
    }

    public static function pow(string $base, string $exponent, ?int $scale = null): string
    {
        $base = static::convertScientificNotationToString($base);
        $exponent = static::convertScientificNotationToString($exponent);

        if (static::isFloat($exponent)) {
            $r = static::powFractional($base, $exponent, $scale);
        } elseif ($scale === null) {
            $r = bcpow($base, $exponent);
        } else {
            $r = bcpow($base, $exponent, $scale);
        }

        return static::formatTrailingZeroes($r);
    }

    protected static function powFractional(string $base, string $exponent, ?int $scale = null): string
    {
        // we need to increased scale to get correct results and avoid rounding error
        $currentScale = $scale ?? static::getScale();
        $increasedScale = $currentScale * 2;

        // add zero to trim scale
        return static::parseNumber(
            static::add(
                static::exp(static::mul($exponent, static::log($base), $increasedScale)),
                '0',
                $currentScale
            )
        );
    }

    public static function getScale(): int
    {
        return bcscale();
    }

    protected static function parseNumber(string $number): string
    {
        $number = str_replace('+', '', (string)filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
        if ($number === '-0' || !is_numeric($number)) {
            return '0';
        }

        return $number;
    }

    public static function add(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if ($scale === null) {
            $r = bcadd($leftOperand, $rightOperand);
        } else {
            $r = bcadd($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r);
    }

    protected static function formatTrailingZeroes(string $number): string
    {
        if (self::$trimTrailingZeroes) {
            return static::trimTrailingZeroes($number);
        }

        return $number;
    }

    protected static function trimTrailingZeroes(string $number): string
    {
        if (static::isFloat($number)) {
            $number = rtrim($number, '0');
        }

        return rtrim($number, '.') ?: '0';
    }

    public static function exp(string $number): string
    {
        $scale = static::DEFAULT_SCALE;
        $result = '1';
        for ($i = 299; $i > 0; --$i) {
            $result = static::add(static::mul(static::div($result, (string)$i, $scale), $number, $scale), '1', $scale);
        }

        return $result;
    }

    public static function mul(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = static::convertScientificNotationToString($leftOperand);
        $rightOperand = static::convertScientificNotationToString($rightOperand);

        if ($scale === null) {
            $r = bcmul($leftOperand, $rightOperand);
        } else {
            $r = bcmul($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r);
    }

    public static function div(string $dividend, string $divisor, ?int $scale = null): string
    {
        $dividend = static::convertScientificNotationToString($dividend);
        $divisor = static::convertScientificNotationToString($divisor);

        if (static::trimTrailingZeroes($divisor) === '0') {
            throw new InvalidArgumentException('Division by zero');
        }

        if ($scale === null) {
            $r = bcdiv($dividend, $divisor);
        } else {
            $r = bcdiv($dividend, $divisor, $scale);
        }

        return static::formatTrailingZeroes((string)$r);
    }

    public static function log(string $number): string
    {
        $number = static::convertScientificNotationToString($number);
        if ($number === '0') {
            return '-INF';
        }
        if (static::COMPARE_RIGHT_GRATER === static::comp($number, '0')) {
            return 'NAN';
        }
        $scale = static::DEFAULT_SCALE;
        $m = (string)log((float)$number);
        $x = static::sub(static::div($number, static::exp($m), $scale), '1', $scale);
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

        if ($scale === null) {
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

        if ($scale === null) {
            $r = bcsub($leftOperand, $rightOperand);
        } else {
            $r = bcsub($leftOperand, $rightOperand, $scale);
        }

        return static::formatTrailingZeroes($r);
    }

    public static function sqrt(string $number, ?int $scale = null): string
    {
        $number = static::convertScientificNotationToString($number);

        if ($scale === null) {
            $r = bcsqrt($number);
        } else {
            $r = bcsqrt($number, $scale);
        }

        return static::formatTrailingZeroes((string)$r);
    }

    public static function setTrimTrailingZeroes(bool $flag): void
    {
        self::$trimTrailingZeroes = $flag;
    }

    /**
     * @param  mixed  $values
     */
    public static function max(...$values): ?string
    {
        $max = null;
        foreach (static::parseValues($values) as $number) {
            $number = static::convertScientificNotationToString((string)$number);
            if ($max === null) {
                $max = $number;
            } elseif (static::comp($max, $number) === static::COMPARE_RIGHT_GRATER) {
                $max = $number;
            }
        }

        return $max;
    }

    protected static function parseValues(array $values): array
    {
        if (is_array($values[0])) {
            $values = $values[0];
        }

        return $values;
    }

    /**
     * @param  mixed  $values
     */
    public static function min(...$values): ?string
    {
        $min = null;
        foreach (static::parseValues($values) as $number) {
            $number = static::convertScientificNotationToString((string)$number);
            if ($min === null) {
                $min = $number;
            } elseif (static::comp($min, $number) === static::COMPARE_LEFT_GRATER) {
                $min = $number;
            }
        }

        return $min;
    }

    public static function powMod(
        string $base,
        string $exponent,
        string $modulus,
        ?int $scale = null
    ): string {
        $base = static::convertScientificNotationToString($base);
        $exponent = static::convertScientificNotationToString($exponent);

        if (static::isNegative($exponent)) {
            throw new InvalidArgumentException('Exponent can\'t be negative');
        }

        if (static::trimTrailingZeroes($modulus) === '0') {
            throw new InvalidArgumentException('Modulus can\'t be zero');
        }

        // bcpowmod don't support floats
        if (
            static::isFloat($base)
            || static::isFloat($exponent)
            || static::isFloat($modulus)
        ) {
            $r = static::mod(static::pow($base, $exponent, $scale), $modulus, $scale);
        } elseif ($scale === null) {
            $r = bcpowmod($base, $exponent, $modulus);
        } else {
            $r = bcpowmod($base, $exponent, $modulus, $scale);
        }

        return static::formatTrailingZeroes((string)$r);
    }

    protected static function isNegative(string $number): bool
    {
        return strncmp('-', $number, 1) === 0;
    }

    public static function mod(string $dividend, string $divisor, ?int $scale = null): string
    {
        // bcmod is not working properly - for example bcmod(9.9999E-10, -0.00056, 9) should return '-0.000559999' but returns 0.0000000
        // let use this $x - floor($x/$y) * $y;
        return static::formatTrailingZeroes(
            static::sub(
                $dividend,
                static::mul(static::floor(static::div($dividend, $divisor, $scale)), $divisor, $scale),
                $scale
            )
        );
    }

    public static function floor(string $number): string
    {
        $number = static::trimTrailingZeroes(static::convertScientificNotationToString($number));
        if (static::isFloat($number)) {
            $result = 0;
            if (static::isNegative($number)) {
                --$result;
            }
            $number = static::add($number, (string)$result, 0);
        }

        return static::parseNumber($number);
    }

    public static function fact(string $number): string
    {
        $number = static::convertScientificNotationToString($number);

        if (static::isFloat($number)) {
            throw new InvalidArgumentException('Number has to be an integer');
        }
        if (static::isNegative($number)) {
            throw new InvalidArgumentException('Number has to be greater than or equal to 0');
        }

        $return = '1';
        for ($i = 2; $i <= $number; ++$i) {
            $return = static::mul($return, (string)$i);
        }

        return $return;
    }

    public static function hexdec(string $hex): string
    {
        $remainingDigits = str_replace('0x', '', substr($hex, 0, -1));
        $lastDigitToDecimal = (string)hexdec(substr($hex, -1));

        if ($remainingDigits === '') {
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

        if (static::isFloat($leftOperand)) {
            throw new InvalidArgumentException('Left operator has to be an integer');
        }
        if (static::isFloat($rightOperand)) {
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
                if ($number === '0') {
                    return chr((int)$number);
                }

                while (self::comp($number, '0') !== self::COMPARE_EQUAL) {
                    $rest = self::mod($number, (string)$base);
                    $number = self::div($number, (string)$base);
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

        return static::parseNumber($number);
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
                    $power = self::pow((string)$base, (string)($size - $i - 1));
                    $return = self::add($return, self::mul((string)$element, $power));
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
        if (!static::isFloat($number)) {
            return $number;
        }

        $precessionPos = strpos($number, '.') + $precision + 1;
        if (strlen($number) <= $precessionPos) {
            return static::round($number, $precision);
        }

        if ($number[-1] !== '5') {
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
        if (static::isFloat($number)) {
            if (static::isNegative($number)) {
                $number = static::sub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            } else {
                $number = static::add($number, '0.' . str_repeat('0', $precision) . '5', $precision);
            }
        }

        return static::parseNumber($number);
    }

    public static function roundUp(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        if (!static::isFloat($number)) {
            return $number;
        }
        $multiply = static::pow('10', (string)abs($precision));

        return static::parseNumber(
            $precision < 0
                ?
                static::mul(
                    static::ceil(static::div($number, $multiply, static::getDecimalsLength($number))),
                    $multiply,
                    (int)abs($precision)
                )
                :
                static::div(
                    static::ceil(static::mul($number, $multiply, static::getDecimalsLength($number))),
                    $multiply,
                    $precision
                )
        );
    }

    public static function ceil(string $number): string
    {
        $number = static::trimTrailingZeroes(static::convertScientificNotationToString($number));
        if (static::isFloat($number)) {
            $result = 1;
            if (static::isNegative($number)) {
                --$result;
            }
            $number = static::add($number, (string)$result, 0);
        }

        return static::parseNumber($number);
    }

    public static function roundDown(string $number, int $precision = 0): string
    {
        $number = static::convertScientificNotationToString($number);
        if (!static::isFloat($number)) {
            return $number;
        }
        $multiply = static::pow('10', (string)abs($precision));

        return static::parseNumber(
            $precision < 0
                ?
                static::mul(
                    static::floor(static::div($number, $multiply, static::getDecimalsLength($number))),
                    $multiply,
                    (int)abs($precision)
                )
                :
                static::div(
                    static::floor(static::mul($number, $multiply, static::getDecimalsLength($number))),
                    $multiply,
                    $precision
                )
        );
    }
}
