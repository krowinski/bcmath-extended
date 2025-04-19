<?php

declare(strict_types=1);

namespace BCMathExtended;

use BcMath\Number;
use Closure;
use InvalidArgumentException;
use RoundingMode;

class BC
{
    public const int COMPARE_EQUAL = 0;
    public const int COMPARE_LEFT_GRATER = 1;
    public const int COMPARE_RIGHT_GRATER = -1;

    protected const int DEFAULT_SCALE = 100;

    protected const int MAX_BASE = 256;

    protected const string BIT_OPERATOR_AND = 'and';
    protected const string BIT_OPERATOR_OR = 'or';
    protected const string BIT_OPERATOR_XOR = 'xor';

    protected static bool $trimTrailingZeroes = true;
    private static ?int $currentScale = null;

    public static function rand(int|string|Number $min, int|string|Number $max): Number
    {
        $max = static::convertToNumber($max);
        $min = static::convertToNumber($min);

        $difference = $max->sub($min)->add(1);
        $randPercent = static::div(mt_rand(), mt_getrandmax(), 8);

        return $difference->mul($randPercent, 8)->add(1, 0);
    }

    public static function convertToNumber(int|string|Number $number): Number
    {
        if ($number instanceof Number) {
            return $number;
        }

        if (is_int($number)) {
            return new Number($number);
        }

        // check if number is in scientific notation, first use stripos as is faster than preg_match
        if (stripos($number, 'E') !== false && preg_match('/(-?(\d+\.)?\d+)E([+-]?)(\d+)/i', $number, $regs)) {
            // calculate final scale of number
            $scale = (int)$regs[4] + static::getDecimalsLength($regs[1]);
            $pow = static::pow(10, $regs[4], $scale);
            if ($regs[3] === '-') {
                $number = static::div($regs[1], $pow, $scale);
            } else {
                $number = static::mul($pow, $regs[1], $scale);
            }
            $number = static::formatTrailingZeroes($number);
        }

        return static::parseToNumber($number);
    }

    public static function getDecimalsLength(int|string|Number $number): int
    {
        if (static::isFloat($number)) {
            return strcspn(strrev((string)$number), '.');
        }

        return 0;
    }

    protected static function isFloat(int|string|Number $number): bool
    {
        return str_contains((string)$number, '.');
    }

    public static function pow(
        int|string|Number $base,
        int|string|Number $exponent,
        ?int $scale = null
    ): Number {
        $base = static::convertToNumber($base);
        $exponent = static::convertToNumber($exponent);

        if (static::isFloat($exponent)) {
            $r = static::powFractional($base, $exponent, self::getScaleForMethod($scale));
        } else {
            $r = $base->pow($exponent, self::getScaleForMethod($scale));
        }

        return static::formatTrailingZeroes($r);
    }

    protected static function powFractional(
        int|string|Number $base,
        int|string|Number $exponent,
        ?int $scale = null
    ): Number {
        // we need to increased scale to get correct results and avoid rounding error
        $currentScale = $scale ?? static::getScale();
        $increasedScale = $currentScale * 2;

        // add zero to trim scale
        return static::parseToNumber(
            static::add(
                static::exp(static::mul($exponent, static::log($base), $increasedScale)),
                0,
                $currentScale
            )
        );
    }

    public static function getScale(): int
    {
        return bcscale();
    }

    protected static function parseToNumber(int|string|Number $number): Number
    {
        if ($number instanceof Number) {
            return $number;
        }

        if (is_int($number)) {
            return new Number($number);
        }

        $number = str_replace(
            '+',
            '',
            (string)filter_var(
                $number,
                FILTER_SANITIZE_NUMBER_FLOAT,
                FILTER_FLAG_ALLOW_FRACTION
            )
        );
        if ($number === '-0' || !is_numeric($number)) {
            $number = 0;
        }
        return new Number($number);
    }

    public static function add(
        int|string|Number $leftOperand,
        int|string|Number $rightOperand,
        ?int $scale = null
    ): Number {
        $leftOperand = static::convertToNumber($leftOperand);
        $rightOperand = static::convertToNumber($rightOperand);

        $r = $leftOperand->add($rightOperand, self::getScaleForMethod($scale));

        return static::formatTrailingZeroes($r);
    }

    protected static function formatTrailingZeroes(Number $number): Number
    {
        if (self::$trimTrailingZeroes) {
            return static::trimTrailingZeroes($number);
        }

        return $number;
    }

    protected static function trimTrailingZeroes(int|string|Number $number): Number
    {
        $number = (string)$number;

        if (static::isFloat($number)) {
            $number = rtrim($number, '0');
        }

        $number = rtrim($number, '.') ?: '0';

        return new Number($number);
    }

    public static function exp(int|string|Number $number): Number
    {
        $number = static::convertToNumber($number);
        $scale = static::DEFAULT_SCALE;
        $result = new Number(1);
        for ($i = 299; $i > 0; --$i) {
            $result = $result->div($i, $scale)->mul($number, $scale)->add(1);
        }

        return self::trimTrailingZeroes($result);
    }

    public static function mul(
        int|string|Number $leftOperand,
        int|string|Number $rightOperand,
        ?int $scale = null
    ): Number {
        $leftOperand = static::convertToNumber($leftOperand);
        $rightOperand = static::convertToNumber($rightOperand);

        $r = $leftOperand->mul($rightOperand, self::getScaleForMethod($scale));

        return static::formatTrailingZeroes($r);
    }

    public static function div(
        int|string|Number $dividend,
        int|string|Number $divisor,
        ?int $scale = null
    ): Number {
        $divisor = static::convertToNumber($divisor);

        if ((string)static::trimTrailingZeroes($divisor) === '0') {
            throw new InvalidArgumentException('Division by zero');
        }

        $r = static::convertToNumber($dividend)->div($divisor, self::getScaleForMethod($scale));

        return static::formatTrailingZeroes($r);
    }

    public static function log(int|string|Number $number): string|Number
    {
        $number = static::convertToNumber($number);
        if ((string)$number === '0') {
            return '-INF';
        }
        if ($number->compare('0') === static::COMPARE_RIGHT_GRATER) {
            return 'NAN';
        }

        $scale = static::DEFAULT_SCALE;
        $m = (string)log((float)(string)$number);
        $x = $number->div(static::exp($m), $scale)->sub(1, $scale);

        $res = new Number(0);
        $pow = new Number(1);

        $i = 1;
        do {
            $pow = $pow->mul($x, $scale);
            $sum = $pow->div($i, $scale);

            if ($i % 2 === 1) {
                $res = $res->add($sum, $scale);
            } else {
                $res = $res->sub($sum, $scale);
            }
            ++$i;
        } while ($sum->compare(0, $scale));

        return self::trimTrailingZeroes($res->add($m, $scale));
    }

    public static function compare(
        int|string|Number $leftOperand,
        int|string|Number $rightOperand,
        ?int $scale = null
    ): int {
        $leftOperand = static::convertToNumber($leftOperand);
        $rightOperand = static::convertToNumber($rightOperand);

        return $leftOperand->compare($rightOperand, self::getScaleForMethod($scale));
    }

    public static function sub(
        int|string|Number $leftOperand,
        int|string|Number $rightOperand,
        ?int $scale = null
    ): Number {
        $leftOperand = static::convertToNumber($leftOperand);
        $rightOperand = static::convertToNumber($rightOperand);

        $r = $leftOperand->sub($rightOperand, self::getScaleForMethod($scale));

        return static::formatTrailingZeroes($r);
    }

    public static function sqrt(int|string|Number $number, ?int $scale = null): Number
    {
        $number = static::convertToNumber($number);

        $r = $number->sqrt(self::getScaleForMethod($scale));

        return static::formatTrailingZeroes($r);
    }

    public static function setTrimTrailingZeroes(bool $flag): void
    {
        self::$trimTrailingZeroes = $flag;
    }

    /**
     * @param mixed $values
     */
    public static function max(...$values): null|Number
    {
        $max = null;
        foreach (static::parseValues($values) as $number) {
            $number = static::convertToNumber((string)$number);
            if ($max === null) {
                $max = $number;
            } elseif ($max->compare($number) === static::COMPARE_RIGHT_GRATER) {
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
     * @param mixed $values
     */
    public static function min(...$values): null|Number
    {
        $min = null;
        foreach (static::parseValues($values) as $number) {
            $number = static::convertToNumber((string)$number);
            if ($min === null) {
                $min = $number;
            } elseif ($min->compare($number) === static::COMPARE_LEFT_GRATER) {
                $min = $number;
            }
        }

        return $min;
    }

    public static function powMod(
        int|string|Number $base,
        int|string|Number $exponent,
        int|string|Number $modulus,
        ?int $scale = null
    ): Number {
        $base = static::convertToNumber($base);
        $exponent = static::convertToNumber($exponent);

        if (static::isNegative($exponent)) {
            throw new InvalidArgumentException('Exponent can\'t be negative');
        }

        if ((string)static::trimTrailingZeroes($modulus) === '0') {
            throw new InvalidArgumentException('Modulus can\'t be zero');
        }

        // bcpowmod don't support floats
        if (static::isFloat($base) || static::isFloat($exponent) || static::isFloat($modulus)) {
            $r = static::mod(
                static::pow(
                    $base,
                    $exponent,
                    self::getScaleForMethod($scale)
                ),
                $modulus,
                self::getScaleForMethod($scale)
            );
        } else {
            $r = $base->powmod($exponent, $modulus, self::getScaleForMethod($scale));
        }

        return static::formatTrailingZeroes($r);
    }

    protected static function isNegative(int|string|Number $number): bool
    {
        return strncmp('-', (string)$number, 1) === 0;
    }

    public static function mod(
        int|string|Number $dividend,
        int|string|Number $divisor,
        ?int $scale = null
    ): Number {
        // bcmod is not working properly - for example bcmod(9.9999E-10, -0.00056, 9) should return '-0.000559999' but returns 0.0000000
        // let use this $x - floor($x/$y) * $y;
        return static::formatTrailingZeroes(
            static::sub(
                $dividend,
                static::mul(
                    static::floor(
                        static::div(
                            $dividend,
                            $divisor,
                            self::getScaleForMethod($scale)
                        )
                    ),
                    $divisor,
                    self::getScaleForMethod($scale)
                ),
                $scale
            )
        );
    }

    public static function floor(int|string|Number $number): Number
    {
        return static::convertToNumber($number)->floor();
    }

    public static function fact(int|string|Number $number): Number
    {
        $number = static::convertToNumber($number);

        if (static::isFloat($number)) {
            throw new InvalidArgumentException('Number has to be an integer');
        }
        if (static::isNegative($number)) {
            throw new InvalidArgumentException('Number has to be greater than or equal to 0');
        }

        $return = new Number(1);
        for ($i = 2; $i <= (int)(string)$number; ++$i) {
            $return = $return->mul($i);
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

        return (string)static::add(
            static::mul(
                16,
                static::hexdec($remainingDigits)
            ),
            $lastDigitToDecimal,
            0
        );
    }

    public static function dechex(int|string|Number $decimal): string
    {
        $quotient = static::div($decimal, 16, 0);
        $remainderToHex = dechex((int)(string)static::mod($decimal, 16));

        if ($quotient->compare(0) === static::COMPARE_EQUAL) {
            return $remainderToHex;
        }

        return static::dechex($quotient) . $remainderToHex;
    }

    public static function bitAnd(int|string|Number $leftOperand, int|string|Number $rightOperand): Number
    {
        return static::bitOperatorHelper($leftOperand, $rightOperand, static::BIT_OPERATOR_AND);
    }

    protected static function bitOperatorHelper(
        int|string|Number $leftOperand,
        int|string|Number $rightOperand,
        string $operator
    ): Number {
        $leftOperand = static::convertToNumber($leftOperand);
        $rightOperand = static::convertToNumber($rightOperand);

        if (static::isFloat($leftOperand)) {
            throw new InvalidArgumentException('Left operator has to be an integer');
        }
        if (static::isFloat($rightOperand)) {
            throw new InvalidArgumentException('Right operator has to be an integer');
        }

        $leftOperandNegative = static::isNegative($leftOperand);
        $rightOperandNegative = static::isNegative($rightOperand);

        $leftOperand = static::dec2bin((string)static::abs($leftOperand));
        $rightOperand = static::dec2bin((string)static::abs($rightOperand));

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

        return new Number($isNegative ? '-' . $result : $result);
    }

    public static function dec2bin(string $number, int $base = self::MAX_BASE): string
    {
        return static::decBaseHelper(
            $base,
            static function (int $base) use ($number): string {
                $value = '';
                if ($number === '0') {
                    return chr((int)$number);
                }

                while (self::compare($number, 0) !== self::COMPARE_EQUAL) {
                    $rest = self::mod($number, $base);
                    $number = self::div($number, $base);
                    $value = chr((int)(string)$rest) . $value;
                }

                return $value;
            }
        );
    }

    /**
     * @param Closure(int): string $closure
     */
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
        self::$currentScale = $scale;
    }

    protected static function getScaleForMethod(?int $scale): ?int
    {
        if ($scale !== null) {
            return $scale;
        }

        return self::$currentScale;
    }

    public static function abs(int|string|Number $number): Number
    {
        $number = static::convertToNumber($number);

        if (static::isNegative($number)) {
            $number = substr((string)$number, 1);
        }

        return static::parseToNumber($number);
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

    public static function bin2dec(int|string|Number $binary, int $base = self::MAX_BASE): string
    {
        $binary = (string)$binary;

        return static::decBaseHelper(
            $base,
            static function (int $base) use ($binary): string {
                $size = strlen($binary);
                $return = '0';

                for ($i = 0; $i < $size; ++$i) {
                    $element = ord($binary[$i]);
                    $power = self::pow($base, $size - $i - 1);
                    $return = self::add($return, self::mul($element, $power));
                }

                return (string)$return;
            }
        );
    }

    public static function bitOr(int|string|Number $leftOperand, int|string|Number $rightOperand): Number
    {
        return static::bitOperatorHelper($leftOperand, $rightOperand, static::BIT_OPERATOR_OR);
    }

    public static function bitXor(int|string|Number $leftOperand, int|string|Number $rightOperand): Number
    {
        return static::bitOperatorHelper($leftOperand, $rightOperand, static::BIT_OPERATOR_XOR);
    }

    public static function roundHalfEven(int|string|Number $number, int $precision = 0): Number
    {
        return static::convertToNumber($number)->round($precision, RoundingMode::HalfEven);
    }

    public static function round(
        int|string|Number $number,
        int $precision = 0,
        RoundingMode $mode = RoundingMode::HalfAwayFromZero
    ): Number {
        $number = static::convertToNumber($number);
        if (static::isFloat($number)) {
            $number = static::formatTrailingZeroes($number->round($precision, $mode));
        }

        return static::parseToNumber($number);
    }

    public static function roundUp(int|string|Number $number, int $precision = 0): Number
    {
        return static::convertToNumber($number)->round($precision, RoundingMode::PositiveInfinity);
    }

    public static function ceil(int|string|Number $number): Number
    {
        return static::convertToNumber($number)->ceil();
    }

    public static function roundDown(int|string|Number $number, int $precision = 0): Number
    {
        return static::convertToNumber($number)->round($precision, RoundingMode::NegativeInfinity);
    }
}
