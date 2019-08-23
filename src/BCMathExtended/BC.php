<?php
declare(strict_types=1);

namespace BCMathExtended;

use Closure;
use InvalidArgumentException;
use function dechex;
use function hexdec;

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

    public static function round(string $number, int $precision = 0): string
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

    public static function convertScientificNotationToString(string $number): string
    {
        // check if number is in scientific notation, first use stripos as is faster then preg_match
        if (false !== stripos($number, 'E') && preg_match('/(-?(\d+\.)?\d+)E([+-]?)(\d+)/i', $number, $regs)) {
            // calculate final scale of number
            $scale = $regs[4] + self::getDecimalsLengthFromNumber($regs[1]);
            $pow = self::pow('10', $regs[4], $scale);
            if ('-' === $regs[3]) {
                $number = self::div($regs[1], $pow, $scale);
            } else {
                $number = self::mul($pow, $regs[1], $scale);
            }
            // remove unnecessary 0 and dot from 0.000 is a 0
            $number = self::trimTrailingZeroes($number);
        }

        return self::checkNumber($number);
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
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (self::checkIsFloat($rightOperand)) {
            if (null === $scale) {
                $r = self::powFractional($leftOperand, $rightOperand);
            } else {
                $r = self::powFractional($leftOperand, $rightOperand, $scale);
            }
        } else if (null === $scale) {
            $r = bcpow($leftOperand, $rightOperand);
        } else {
            $r = bcpow($leftOperand, $rightOperand, $scale);
        }

        return self::trimTrailingZeroes($r);
    }

    protected static function checkIsFloat(string $number): bool
    {
        return false !== strpos($number, '.');
    }

    protected static function powFractional(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        // we need to increased scale to get correct results and avoid rounding error
        $increasedScale = $scale ?? self::getScale();
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

    public static function getScale(): int
    {
        if (PHP_VERSION_ID >= 70300) {
            return bcscale();
        }

        $sqrt = self::sqrt('2');

        return strlen(substr($sqrt, strpos($sqrt, '.') + 1));
    }

    public static function sqrt(string $operand, ?int $scale = null): string
    {
        $operand = self::convertScientificNotationToString($operand);

        if (null === $scale) {
            $r = bcsqrt($operand);
        } else {
            $r = bcsqrt($operand, $scale);
        }

        return self::trimTrailingZeroes($r);
    }

    protected static function trimTrailingZeroes(string $number): string
    {
        if (false !== strpos($number, '.')) {
            $number = rtrim($number, '0');
        }

        return rtrim($number, '.') ?: '0';
    }

    protected static function checkNumber(string $number): string
    {
        $number = str_replace('+', '', filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
        if ('-0' === $number || !is_numeric($number)) {
            return '0';
        }

        return $number;
    }

    public static function mul(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            $r = bcmul($leftOperand, $rightOperand);
        } else {
            $r = bcmul($leftOperand, $rightOperand, $scale);
        }

        return self::trimTrailingZeroes($r);
    }

    public static function exp(string $arg): string
    {
        $scale = self::DEFAULT_SCALE;
        $result = '1';
        for ($i = 299; $i > 0; $i--) {
            $result = self::add(self::mul(self::div($result, (string)$i, $scale), $arg, $scale), '1', $scale);
        }

        return $result;
    }

    public static function add(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            $r = bcadd($leftOperand, $rightOperand);
        } else {
            $r = bcadd($leftOperand, $rightOperand, $scale);
        }

        return self::trimTrailingZeroes($r);
    }

    public static function div(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if ('0' === self::trimTrailingZeroes($rightOperand)) {
            throw new InvalidArgumentException('Division by zero');
        }

        if (null === $scale) {
            $r = bcdiv($leftOperand, $rightOperand);
        } else {
            $r = bcdiv($leftOperand, $rightOperand, $scale);
        }

        return self::trimTrailingZeroes($r);
    }

    public static function log(string $arg): string
    {
        $arg = self::convertScientificNotationToString($arg);
        if ($arg === '0') {
            return '-INF';
        }
        if (self::COMPARE_RIGHT_GRATER === self::comp($arg, '0')) {
            return 'NAN';
        }
        $scale = self::DEFAULT_SCALE;
        $m = (string)log((float)$arg);
        $x = self::sub(self::div($arg, self::exp($m), $scale), '1', $scale);
        $res = '0';
        $pow = '1';
        $i = 1;
        do {
            $pow = self::mul($pow, $x, $scale);
            $sum = self::div($pow, (string)$i, $scale);
            if ($i % 2 === 1) {
                $res = self::add($res, $sum, $scale);
            } else {
                $res = self::sub($res, $sum, $scale);
            }
            $i++;
        } while (self::comp($sum, '0', $scale));

        return self::add($res, $m, $scale);
    }

    public static function comp(string $leftOperand, string $rightOperand, ?int $scale = null): int
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

    public static function sub(string $leftOperand, string $rightOperand, ?int $scale = null): string
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (null === $scale) {
            $r = bcsub($leftOperand, $rightOperand);
        } else {
            $r = bcsub($leftOperand, $rightOperand, $scale);
        }

        return self::trimTrailingZeroes($r);
    }

    protected static function isNegative(string $number): bool
    {
        return 0 === strncmp('-', $number, 1);
    }

    public static function rand(string $min, string $max): string
    {
        $max = self::convertScientificNotationToString($max);
        $min = self::convertScientificNotationToString($min);

        $difference = self::add(self::sub($max, $min), '1');
        $randPercent = self::div((string)mt_rand(), (string)mt_getrandmax(), 8);

        return self::add($min, self::mul($difference, $randPercent, 8), 0);
    }

    public static function max(...$ags): ?string
    {
        $max = null;
        foreach (self::parseArgs($ags) as $number) {
            $number = self::convertScientificNotationToString((string)$number);
            if (null === $max) {
                $max = $number;
            } elseif (self::comp((string)$max, $number) === self::COMPARE_RIGHT_GRATER) {
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
        foreach (self::parseArgs($ags) as $number) {
            $number = self::convertScientificNotationToString((string)$number);
            if (null === $min) {
                $min = $number;
            } elseif (self::comp((string)$min, $number) === self::COMPARE_LEFT_GRATER) {
                $min = $number;
            }
        }

        return $min;
    }

    public static function roundDown(string $number, int $precision = 0): string
    {
        $number = self::convertScientificNotationToString($number);
        $multiply = self::pow('10', (string)abs($precision));

        return $precision < 0
            ?
            self::mul(
                self::floor(self::div($number, $multiply, self::getDecimalsLengthFromNumber($number))), $multiply,
                $precision
            )
            :
            self::div(
                self::floor(self::mul($number, $multiply, self::getDecimalsLengthFromNumber($number))), $multiply,
                $precision
            );
    }

    public static function floor(string $number): string
    {
        $number = self::convertScientificNotationToString($number);
        if (self::checkIsFloat($number) && self::checkIsFloatCleanZeros($number)) {
            $result = 0;
            if (self::isNegative($number)) {
                --$result;
            }
            $number = self::add($number, (string)$result, 0);
        }

        return self::checkNumber($number);
    }

    protected static function checkIsFloatCleanZeros(string &$number): bool
    {
        return false !== strpos($number = self::trimTrailingZeroes($number), '.');
    }

    public static function roundUp(string $number, int $precision = 0): string
    {
        $number = self::convertScientificNotationToString($number);
        $multiply = self::pow('10', (string)abs($precision));

        return $precision < 0
            ?
            self::mul(
                self::ceil(self::div($number, $multiply, self::getDecimalsLengthFromNumber($number))), $multiply,
                $precision
            )
            :
            self::div(
                self::ceil(self::mul($number, $multiply, self::getDecimalsLengthFromNumber($number))), $multiply,
                $precision
            );
    }

    public static function ceil(string $number): string
    {
        $number = self::convertScientificNotationToString($number);
        if (self::checkIsFloat($number) && self::checkIsFloatCleanZeros($number)) {
            $result = 1;
            if (self::isNegative($number)) {
                --$result;
            }
            $number = self::add($number, (string)$result, 0);
        }

        return self::checkNumber($number);
    }

    public static function powMod(
        string $leftOperand,
        string $rightOperand,
        string $modulus,
        ?int $scale = null
    ): string {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        // bcpowmod in 5.6 have don't calculate correct results if scale is empty
        if (null === $scale) {
            $r = self::mod(self::pow($leftOperand, $rightOperand), $modulus);
        } else if (self::checkIsFloat($leftOperand) || self::checkIsFloat($rightOperand) || self::checkIsFloat(
                $modulus
            )) {
            // cant use bcpowmod here as it don't support floats
            $r = self::mod(self::pow($leftOperand, $rightOperand, $scale), $modulus, $scale);
        } else {
            $r = bcpowmod($leftOperand, $rightOperand, $modulus, $scale);
        }

        return self::trimTrailingZeroes($r);
    }

    public static function mod(string $leftOperand, string $modulus, ?int $scale = null): string
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);

        // bcmod in 7.2 is not working properly - for example bcmod(9.9999E-10, -0.00056, 9) should return '-0.000559999' but returns 0.0000000

        // bcmod in php 5.6< don't support scale and floats
        // let use this $x - floor($x/$y) * $y;
        if (null === $scale) {
            $r = self::sub(
                $leftOperand,
                self::mul(self::floor(self::div($leftOperand, $modulus)), $modulus)
            );
        } else {
            $r = self::sub(
                $leftOperand,
                self::mul(self::floor(self::div($leftOperand, $modulus, $scale)), $modulus, $scale),
                $scale
            );
        }

        return self::trimTrailingZeroes($r);
    }

    public static function fact(string $arg): string
    {
        $arg = self::convertScientificNotationToString($arg);

        if (self::checkIsFloat($arg)) {
            throw new InvalidArgumentException('Number has to be an integer');
        }
        if (self::isNegative($arg)) {
            throw new InvalidArgumentException('Number has to be greater than or equal to 0');
        }

        $return = '1';
        for ($i = 2; $i <= $arg; ++$i) {
            $return = self::mul($return, (string)$i);
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

        return self::add(self::mul('16', self::hexdec($remainingDigits)), $lastDigitToDecimal, 0);
    }

    public static function dechex(string $decimal): string
    {
        $quotient = self::div($decimal, '16', 0);
        $remainderToHex = dechex((int)self::mod($decimal, '16'));

        if (self::comp($quotient, '0') === self::COMPARE_EQUAL) {
            return $remainderToHex;
        }

        return self::dechex($quotient) . $remainderToHex;
    }

    public static function bitAnd(
        string $leftOperand,
        string $rightOperand
    ): string {
        return self::bitOperatorHelper($leftOperand, $rightOperand, self::BIT_OPERATOR_AND);
    }

    protected static function bitOperatorHelper(string $leftOperand, string $rightOperand, string $operator): string
    {
        $leftOperand = self::convertScientificNotationToString($leftOperand);
        $rightOperand = self::convertScientificNotationToString($rightOperand);

        if (self::checkIsFloat($leftOperand)) {
            throw new InvalidArgumentException('Left operator has to be an integer');
        }
        if (self::checkIsFloat($rightOperand)) {
            throw new InvalidArgumentException('Right operator has to be an integer');
        }

        $leftOperandNegative = self::isNegative($leftOperand);
        $rightOperandNegative = self::isNegative($rightOperand);

        $leftOperand = self::dec2bin(self::abs($leftOperand));
        $rightOperand = self::dec2bin(self::abs($rightOperand));

        $maxLength = max(strlen($leftOperand), strlen($rightOperand));

        $leftOperand = self::alignBinLength($leftOperand, $maxLength);
        $rightOperand = self::alignBinLength($rightOperand, $maxLength);

        if ($leftOperandNegative) {
            $leftOperand = self::recalculateNegative($leftOperand);
        }
        if ($rightOperandNegative) {
            $rightOperand = self::recalculateNegative($rightOperand);
        }

        $isNegative = false;
        $result = '';
        if (self::BIT_OPERATOR_AND === $operator) {
            $result = $leftOperand & $rightOperand;
            $isNegative = ($leftOperandNegative and $rightOperandNegative);
        } elseif (self::BIT_OPERATOR_OR === $operator) {
            $result = $leftOperand | $rightOperand;
            $isNegative = ($leftOperandNegative or $rightOperandNegative);
        } elseif (self::BIT_OPERATOR_XOR === $operator) {
            $result = $leftOperand ^ $rightOperand;
            $isNegative = ($leftOperandNegative xor $rightOperandNegative);
        }

        if ($isNegative) {
            $result = self::recalculateNegative($result);
        }

        $result = self::bin2dec($result);

        return $isNegative ? '-' . $result : $result;
    }

    public static function dec2bin(string $number, int $base = self::MAX_BASE): string
    {
        return self::decBaseHelper(
            $base, static function (int $base) use ($number) {
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
        if ($base < 2 || $base > self::MAX_BASE) {
            throw new InvalidArgumentException('Invalid Base: ' . $base);
        }
        $orgScale = self::getScale();
        self::setScale(0);

        $value = $closure($base);

        self::setScale($orgScale);

        return $value;
    }

    public static function setScale(int $scale): void
    {
        bcscale($scale);
    }

    public static function abs(string $number): string
    {
        $number = self::convertScientificNotationToString($number);

        if (self::isNegative($number)) {
            $number = (string)substr($number, 1);
        }

        return self::checkNumber($number);
    }

    protected static function alignBinLength(string $string, int $length): string
    {
        return str_pad($string, $length, self::dec2bin('0'), STR_PAD_LEFT);
    }

    protected static function recalculateNegative(string $number): string
    {
        $xor = str_repeat(self::dec2bin((string)(self::MAX_BASE - 1)), strlen($number));
        $number ^= $xor;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $byte = ord($number[$i]);
            if (++$byte !== self::MAX_BASE) {
                $number[$i] = chr($byte);
                break;
            }
        }

        return $number;
    }

    public static function bin2dec(string $binary, int $base = self::MAX_BASE): string
    {
        return self::decBaseHelper(
            $base, static function (int $base) use ($binary) {
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
        return self::bitOperatorHelper($leftOperand, $rightOperand, self::BIT_OPERATOR_OR);
    }

    public static function bitXor(string $leftOperand, string $rightOperand): string
    {
        return self::bitOperatorHelper($leftOperand, $rightOperand, self::BIT_OPERATOR_XOR);
    }

    public static function roundHalfEven(string $number, int $precision = 0): string
    {
        $number = self::convertScientificNotationToString($number);
        if (! self::checkIsFloat($number)) {
            return self::checkNumber($number);
        }

        $precessionPos = strpos($number, '.') + $precision + 1;
        if (strlen($number) <= $precessionPos) {
            return self::round($number, $precision);
        }

        if ($number[$precessionPos] !== '5') {
            return self::round($number, $precision);
        }

        $isPrevEven = $number[$precessionPos - 1] === '.'
            ? (int)$number[$precessionPos - 2] % 2 === 0
            : (int)$number[$precessionPos - 1] % 2 === 0
        ;
        $isNegative = self::isNegative($number);

        if ($isPrevEven === $isNegative) {
            return self::roundUp($number, $precision);
        }

        return self::roundDown($number, $precision);
    }
}
