<?php

namespace BCMathExtended\Tests\Unit;

use BCMathExtended\BC;
use PHPUnit\Framework\TestCase;

/**
 * Class BCTest
 * @package BCMathExtended\Tests\Unit
 */
class BCTest extends TestCase
{
    /**
     * @return array
     */
    public function scientificNotationProvider()
    {
        return [
            ['0', '-0'],
            ['0', ''],
            ['666', '666'],
            ['-666', '-666'],
            [
                '99999999999999999999999999999999999.000000000000000000000',
                '99999999999999999999999999999999999.000000000000000000000',
            ],
            [
                '99999999999999999999999999999999999.999999999999999999999',
                '99999999999999999999999999999999999.999999999999999999999',
            ],
            ['1000000000000000000000000000000', 1.0E+30],
            ['-1540000000000000', -1.54E+15],
            ['1540000000000000', 1.54E+15],
            ['602200000000000000000000', '6.022E+23'],
            ['602200000000000000000000', '6.022e+23'],
            ['-602200000000000000000000', '-6.022e+23'],
            ['-602200000000000000000000', '-6.022E+23'],
            ['1999.99', '19.9999E+2'],
            ['0.00000000001', 1.0E-11],
            ['0.0000051', 5.1E-6],
            ['-0.00051', -5.1E-4],
            ['0.02', 2E-2],
            ['0.0021', 2.1E-3],
            ['0.00000003', 3E-8],
            ['0.000000657', 6.57E-7],
            ['5', '5e+0'],
            ['-5', '-5e+0'],
            ['5.254', 5.254e+0],
            ['8853.6719', 8.8536719e+3],
            ['0.00000000001', '0.00000000001'],
            ['-0.00116000', '-0.00116000'],
            ['-26.2912940386', -2.62912940386e+1],
            ['2.6', 2.6e+0],
            ['1734825599220.52', '1.73482559922052e+12'],
            ['-57170562.129942072027205098329198887303', '-5.7170562129942072027205098329198887303e+7'],
            ['0.000021', '2.1e-5'],
            ['0.7811084054', '7.811084054e-1'],
            ['0', '0e+0'],
            ['-1.1', '-1.1e+0'],
            ['-4.182', '-4.182e+0'],
            ['23.07', '2.307e+1'],
            ['-2349', '-2.349e+3'],
            ['-230807.1307795', '-2.308071307795e+5'],
            ['-887126.1', '-8.871261e+5'],
            [
                '-0.40559318155029357183161762311247760893712676112986144952',
                '-4.0559318155029357183161762311247760893712676112986144952e-1',
            ],
            [
                '-749168762.7771507445838618797279002344648652959333491',
                '-7.491687627771507445838618797279002344648652959333491e+8',
            ],
            ['1.3333', '0.13333e+01'],
            // some rubbish..
            ['23', '23.1.23.0e+0..3123131'], //hmm
            ['345600000000', '3.456e11'],
            ['345600000000', 3.456e11],
            ['-345600000000', '-3.456e11'],
            ['-345600000000', -3.456e11],

        ];
    }

    /**
     * @test
     * @dataProvider scientificNotationProvider
     * @param string $expected
     * @param mixed $number
     */
    public function shouldConvertScientificNotationToString($expected, $number)
    {
        $number = BC::convertScientificNotationToString($number);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @return array
     */
    public function ceilProvider()
    {
        return [
            ['0', -0],
            ['-1', -1],
            ['-1', -1.5],
            ['-1', -1.8],
            ['-2', -2.7],
            ['0', 0],
            ['1', 0.5],
            ['1', 1],
            ['2', 1.5],
            ['2', 1.8],
            ['3', 2.7],
            ['0', '-0'],
            ['0', ''],
            ['0', null],
            ['20000', '2/0000'],
            ['-60000', '-6/0000'],
            ['1000000000000000000000000000000', '+1/000000000000000000000000000000'],
            ['99999999999999999999999999999999999', '99999999999999999999999999999999999.000000000000000000000'],
            ['100000000000000000000000000000000000', '99999999999999999999999999999999999.999999999999999999999'],
            ['0', '0-'],
            ['100000000000000000000000000000000000', 1.0E+35],
            ['-100000000000000000000000000000000000', -1.0E+35],
            ['1', 3E-8],
            ['1', 1.0E-11],
        ];
    }

    /**
     * @test
     * @param string $expected
     * @param mixed $number
     * @dataProvider ceilProvider
     */
    public function shouldCeil($expected, $number)
    {
        $number = BC::ceil($number);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @return array
     */
    public function floorProvider()
    {
        return [
            ['0', -0],
            ['-1', -0.5],
            ['-1', -1],
            ['-2', -1.5],
            ['-2', -1.8],
            ['-3', -2.7],
            ['0', 0],
            ['0', 0.5],
            ['1', 1],
            ['1', 1.5],
            ['1', 1.8],
            ['2', 2.7],
            ['0', '-0'],
            ['0', ''],
            ['0', null],
            ['20000', '2/0000'],
            ['-60000', '-6/0000'],
            ['1000000000000000000000000000000', '+1/000000000000000000000000000000'],
            [
                '99999999999999999999999999999999999',
                '99999999999999999999999999999999999.000000000000000000000',
            ],
            [
                '99999999999999999999999999999999999',
                '99999999999999999999999999999999999.999999999999999999999',
            ],
            ['0', '0-'],
            ['100000000000000000000000000000000000', 1.0E+35],
            ['-100000000000000000000000000000000000', -1.0E+35],
            ['0', 3E-8],
            ['0', 1.0E-11],
        ];
    }

    /**
     * @test
     * @dataProvider floorProvider
     * @param string $expected
     * @param int|float|string $number
     */
    public function shouldFloor($expected, $number)
    {
        $number = BC::floor($number);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @return array
     */
    public function absProvider()
    {
        return [
            ['1', -1],
            ['1.5', -1.5],
            ['1', '-1'],
            ['1.5', '-1.5'],
            [
                '9999999999999999999999999999999999999999999999999999999',
                '-9999999999999999999999999999999999999999999999999999999',
            ],
            ['0', '-0'],
            ['0', ''],
            ['0', null],
            ['20000', '2/0000'],
            ['60000', '-6/0000'],
            ['1000000000000000000000000000000', '+1/000000000000000000000000000000'],
            ['0', '0-'],
            ['100000000000000000000000000000000000', 1.0E+35],
            ['100000000000000000000000000000000000', -1.0E+35],
            ['0.0000051', -5.1E-6],
        ];
    }

    /**
     * @test
     * @dataProvider absProvider
     * @param string $expected
     * @param int|float|string $number
     */
    public function shouldAbs($expected, $number)
    {
        $number = BC::abs($number);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @return array
     */
    public function roundProvider()
    {
        return [
            ['3', '3.4'],
            ['4', '3.5'],
            ['4', '3.6'],
            ['2', '1.95583'],
            ['2', '1.95583'],
            ['1.96', '1.95583', 2],
            ['1.956', '1.95583', 3],
            ['1.9558', '1.95583', 4],
            ['1.95583', '1.95583', 5],
            ['1241757', '1241757'],
            ['1241757', '1241757', 5],
            ['-3', '-3.4'],
            ['-4', '-3.5'],
            ['-4', '-3.6'],
            ['123456.745671', '123456.7456713', 6],
            ['1', '1.11'],
            ['1.11', '1.11', 2],
            ['0.1666666666667', '0.1666666666666665', 13],
            ['0', '0.1666666666666665', 0.13],
            ['10', '9.999'],
            ['10.00', '9.999', 2],
            ['0.01', '0.005', 2],
            ['0.02', '0.015', 2],
            ['0.03', '0.025', 2],
            ['0.04', '0.035', 2],
            ['0.05', '0.045', 2],
            ['0.06', '0.055', 2],
            ['0.07', '0.065', 2],
            ['0.08', '0.075', 2],
            ['0.09', '0.085', 2],
            ['77777777777777777777777777777', '77777777777777777777777777777.1'],
            [
                '100000000000000000000000000000000000',
                '99999999999999999999999999999999999.99999999999999999999999999999999991',
            ],
            [
                '99999999999999999999999999999999999',
                '99999999999999999999999999999999999.00000000000000000000000000000000001',
            ],
            ['99999999999999999999999999999999999', '99999999999999999999999999999999999.000000000000000000000'],
            ['0', '-0'],
            ['0', ''],
            ['0', null],
            ['20000', '2/0000'],
            ['-60000', '-6/0000'],
            ['1000000000000000000000000000000', '+1/000000000000000000000000000000'],
            ['0', '0-'],
            ['100000000000000000000000000000000000', 1.0E+35],
            ['-100000000000000000000000000000000000', -1.0E+35],
            ['0', 3E-8],
            ['0', 1.0E-11],
            ['-0.0006', -5.6E-4, 4],
            ['0.0000000010', 9.9999E-10, 10],
        ];
    }

    /**
     * @test
     * @dataProvider roundProvider
     * @param string $expected
     * @param int|float|string $number
     * @param int $precision
     */
    public function shouldRound($expected, $number, $precision = 0)
    {
        $number = BC::round($number, $precision);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @return array
     */
    public function randProvider()
    {
        return [
            [1, 3],
            ['432423432423423423423423432432423423423', '999999999999999999999999999999999999999999'],
        ];
    }

    /**
     * @test
     * @param string|int $left
     * @param string|int $right
     * @dataProvider randProvider
     */
    public function shouldRand($left, $right)
    {
        $rand = BC::rand($left, $right);
        self::assertInternalType('string', $rand);
        self::assertTrue($rand >= $left);
        self::assertTrue($rand <= $right);
    }

    /**
     * @test
     */
    public function shouldMax()
    {
        self::assertSame('3', BC::max(1, 2, 3));
        self::assertSame('6', BC::max(6, 3, 2));
        self::assertSame('999', BC::max(100, 999, 5));

        self::assertSame('677', BC::max([3, 5, 677]));
        self::assertSame('-3', BC::max([-3, -5, -677]));

        self::assertSame(
            '999999999999999999999999999999999999999999',
            BC::max(
                '432423432423423423423423432432423423423',
                '999999999999999999999999999999999999999999',
                '321312312423435657'
            )
        );
        self::assertSame('0.00000000099999', BC::max(9.9999E-10, -5.6E-4));
    }

    /**
     * @test
     */
    public function shouldMin()
    {
        self::assertSame('7.20', BC::min('7.30', '7.20'));
        self::assertSame('3', BC::min([3, 5, 677]));
        self::assertSame('-677', BC::min([-3, -5, -677]));

        self::assertSame(
            '321312312423435657',
            BC::min(
                '432423432423423423423423432432423423423',
                '999999999999999999999999999999999999999999',
                '321312312423435657'
            )
        );

        self::assertSame('-0.00056', BC::min(9.9999E-10, -5.6E-4));
    }

    /**
     * @return array
     */
    public function setScaleProvider()
    {
        return [
            [50, '3.00000000000000000000000000000000000000000000000000', '1', '2'],
            [null, '3', '1', '2'],
            [13, '3.0000000000000', '1', '2'],
        ];
    }

    /**
     * @test
     * @dataProvider setScaleProvider
     * @param int|null $scale
     * @param string $expected
     * @param string $left
     * @param string $right
     */
    public function shouldSetScale($scale, $expected, $left, $right)
    {
        BC::setScale($scale);
        self::assertSame($expected, BC::add($left, $right));
    }

    /**
     * @return array
     */
    public function roundUpProvider()
    {
        return [
            ['663', 662.79],
            ['662.8', 662.79, 1],
            ['60', 54.1, -1],
            ['60', 55.1, -1],
            ['-23.6', -23.62, 1],
            ['4', 3.2],
            ['77', 76.9],
            ['3.142', 3.14159, 3],
            ['-3.1', -3.14159, 1],
            ['31500', 31415.92654, -2],
            ['31420', 31415.92654, -1],
            ['0.0119', 0.0119, 4],
            ['0', '-0'],
            ['0', ''],
            ['0', null],
            ['0', '0-'],
            ['1', 9.9999E-10],
        ];
    }

    /**
     * @test
     * @dataProvider roundUpProvider
     * @param string $expected
     * @param int|float|string $number
     * @param int $precision
     */
    public function shouldRoundUp($expected, $number, $precision = 0)
    {
        $number = BC::roundUp($number, $precision);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @return array
     */
    public function roundDownProvider()
    {
        return [
            ['662', 662.79],
            ['662.7', 662.79, 1],
            ['50', 54.1, -1],
            ['50', 55.1, -1],
            ['-23.7', -23.62, 1],
            ['3', 3.2],
            ['76', 76.9],
            ['3.141', 3.14159, 3],
            ['-3.2', -3.14159, 1],
            ['31400', 31415.92654, -2],
            ['31410', 31415.92654, -1],
            ['0.0119', 0.0119, 4],
            ['0', '-0'],
            ['0', ''],
            ['0', null],
            ['0', '0-'],
            ['0', 9.9999E-10],
        ];
    }

    /**
     * @test
     * @dataProvider roundDownProvider
     * @param string $expected
     * @param int|float|string $number
     * @param int $precision
     */
    public function shouldRoundDown($expected, $number, $precision = 0)
    {
        $number = BC::roundDown($number, $precision);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @return array
     */
    public function addProvider()
    {
        return [
            ['3', '1', '2'],
            ['2', '1', '1'],
            ['15', '10', '5'],
            ['2.05', '1', '1.05', 2],
            ['4.0000', '-1', '5', 4],
            ['8728932003911564969352217864684.00', '1928372132132819737213', '8728932001983192837219398127471', 2],
            ['-0.00055999', 9.9999E-10, -5.6E-4, 8],
            ['15.000000000000311', '3.11e-13', '15', 15],
            ['3110000015', '3.11e9', '15', 0],
        ];
    }

    /**
     * @test
     * @param int|null $scale
     * @param string $expected
     * @param string $left
     * @param string $right
     * @dataProvider addProvider
     */
    public function shouldAdd($expected, $left, $right, $scale = 0)
    {
        $number = BC::add($left, $right, $scale);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @test
     */
    public function shouldAddUsingGlobalScale()
    {
        BC::setScale(0);
        self::assertSame('2', BC::add('1', '1.05'));
        self::assertSame('2.05', BC::add('1', '1.05', 2));
        BC::setScale(2);
        self::assertSame('2', BC::add('1', '1.05', 0));
        self::assertSame('2.05', BC::add('1', '1.05'));
    }

    /**
     * @test
     */
    public function shouldSubUsingGlobalScale()
    {
        BC::setScale(0);
        self::assertSame('-1', BC::sub('1', '2.5'));
        self::assertSame('-1.50', BC::sub('1', '2.5', 2));
        BC::setScale(2);
        self::assertSame('-1', BC::sub('1', '2.5', 0));
        self::assertSame('-1.50', BC::sub('1', '2.5'));
    }

    /**
     * @return array
     */
    public function subProvider()
    {
        return [
            ['-1', '1', '2'],
            ['0', '1', '1'],
            ['5', '10', '5'],
            ['-1.50', '1', '2.5', 2],
            ['-6.0000', '-1', '5', 4],
            ['8728932000054820705086578390258.00', '8728932001983192837219398127471', '1928372132132819737213', 2],
            ['0.00056000', 9.9999E-10, -5.6E-4, 8],
        ];
    }

    /**
     * @test
     * @param int|null $scale
     * @param string $expected
     * @param string $left
     * @param string $right
     * @dataProvider subProvider
     */
    public function shouldSub($expected, $left, $right, $scale = 0)
    {
        $number = BC::sub($left, $right, $scale);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @return array
     */
    public function compProvider()
    {
        return [
            ['-1', '5', BC::COMPARE_RIGHT_GRATER, 4],
            ['1928372132132819737213', '8728932001983192837219398127471', BC::COMPARE_RIGHT_GRATER, 1],
            ['1.00000000000000000001', '2', BC::COMPARE_RIGHT_GRATER, 1],
            [97321, 1, BC::COMPARE_LEFT_GRATER, 2],
            [1, 0, BC::COMPARE_LEFT_GRATER, 0],
            [1, 1, BC::COMPARE_EQUAL, 0],
            [0, 1, BC::COMPARE_RIGHT_GRATER, 0],
            ['1', '0', BC::COMPARE_LEFT_GRATER, 0],
            ['1', '1', BC::COMPARE_EQUAL, 0],
            ['0', '1', BC::COMPARE_RIGHT_GRATER, 0],
            ['1', '0.0005', BC::COMPARE_LEFT_GRATER, 4],
            ['1', '0.000000000000000000000000005', BC::COMPARE_LEFT_GRATER, null],
        ];
    }

    /**
     * @test
     * @dataProvider compProvider
     * @param string|int $left
     * @param string|int $right
     * @param int $expected
     * @param int $scale
     */
    public function shouldComp($left, $right, $expected, $scale)
    {
        $operator = BC::comp($left, $right, $scale);
        self::assertInternalType('int', $operator);
        self::assertSame($expected, $operator);
    }

    /**
     * @return array
     */
    public function getScaleProvider()
    {
        return [
            [10],
            [25],
            [0],
        ];
    }

    /**
     * @test
     * @param int $expected
     * @dataProvider getScaleProvider
     */
    public function shouldGetScale($expected)
    {
        BC::setScale($expected);
        $scale = BC::getScale();
        self::assertInternalType('int', $scale);
        self::assertSame($expected, $scale);
    }

    public function divProvider()
    {
        return [
            ['0.50', '1', '2', 2],
            ['-0.2000', '-1', '5', 4],
            ['4526580661.75', '8728932001983192837219398127471', '1928372132132819737213', 2],
            ['0.000000000099999', '9.9999E-10', '10', 15],
        ];
    }

    /**
     * @test
     * @dataProvider divProvider
     * @param string $expected
     * @param string $left
     * @param string $right
     * @param int $scale
     */
    public function shouldDiv($expected, $left, $right, $scale)
    {
        $number = BC::div($left, $right, $scale);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @test
     */
    public function shouldDivUsingGlobalScale()
    {
        BC::setScale(0);
        self::assertSame('0', BC::div('1', '2'));
        self::assertSame('0.50', BC::div('1', '2', 2));
        BC::setScale(2);
        self::assertSame('0', BC::div('1', '2', 0));
        self::assertSame('0.50', BC::div('1', '2'));
    }

    public function modProvider()
    {
        return [
            ['1', '11', '2'],
            ['-1', '-1', '5'],
            ['1459434331351930289678', '8728932001983192837219398127471', '1928372132132819737213'],
            ['0', 9.9999E-10, 1],
        ];
    }

    /**
     * @test
     * @dataProvider modProvider
     * @param string $expected
     * @param string $left
     * @param string $right
     */
    public function shouldMod($expected, $left, $right)
    {
        $number = BC::mod($left, $right);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    public function fmodProvider()
    {
        return [
            ['0.8', '10', '9.2', 1],
            ['0.0', '20', '4.0', 1],
            ['0.0', '10.5', '3.5', 1],
            ['0.3', '10.2', '3.3', 1],
            ['-0.000559999', 9.9999E-10, -5.6E-4, 9],
        ];
    }

    /**
     * @test
     * @dataProvider fmodProvider
     * @param string $expected
     * @param string $left
     * @param string $right
     * @param int $scale
     */
    public function shouldFmod($expected, $left, $right, $scale)
    {
        $number = BC::fmod($left, $right, $scale);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @test
     */
    public function shouldFmodUsingGlobalScale()
    {
        BC::setScale(0);
        self::assertSame('1', BC::fmod('10', '9.2'));
        self::assertSame('0.80', BC::fmod('10', '9.2', 2));
        BC::setScale(2);
        self::assertSame('1', BC::fmod('10', '9.2', 0));
        self::assertSame('0.80', BC::fmod('10', '9.2'));
    }

    /**
     * @return array
     */
    public function mulProvider()
    {
        return [
            ['1', '1.5', '1.5', 1],
            ['10', '1.2500', '12.50', 2],
            ['100', '0.29', '29', 0],
            ['100', '0.029', '2.9', 1],
            ['100', '0.0029', '0.29', 2],
            ['1000', '0.29', '290', 0],
            ['1000', '0.029', '29', 0],
            ['1000', '0.0029', '2.9', 1],
            ['2000', '0.0029', '5.8', 1],
            ['1', '2', '2', null],
            ['-3', '5', '-15', null],
            ['1234567890', '9876543210', '12193263111263526900', null],
            ['2.5', '1.5', '3.75', 2],
            ['2.555', '1.555', '3.97', 2],
            [9.9999E-2, -5.6E-2, '-0.005599944', 9],
        ];
    }

    /**
     * @test
     * @dataProvider mulProvider
     * @param string $leftOperand
     * @param string $rightOperand
     * @param string $expected
     * @param null|int $scale
     */
    public function shouldMul($leftOperand, $rightOperand, $expected, $scale)
    {
        $number = BC::mul($leftOperand, $rightOperand, $scale);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @test
     */
    public function shouldMulUsingGlobalScale()
    {
        BC::setScale(0);
        self::assertSame('1', BC::fmod('1.5', '3.75'));
        self::assertSame('1.50', BC::fmod('1.5', '3.75', 2));
        BC::setScale(2);
        self::assertSame('1', BC::fmod('1.5', '3.75', 0));
        self::assertSame('1.50', BC::fmod('1.5', '3.75'));
    }

    /**
     * @return array
     */
    public function powProvider()
    {
        return [
            ['74.08', '4.2', '3', 2],
            ['-32', '-2', '5', 4],
            ['18446744073709551616', '2', '64'],
            ['-108.88', '-2.555', '5', 2],
            ['63998080023999840000.599998800', '19.9999E+2', '6', 9],
        ];
    }

    /**
     * @test
     * @dataProvider powProvider
     * @param string $expected
     * @param string $left
     * @param string $right
     * @param null|int $scale
     */
    public function shouldPow($expected, $left, $right, $scale = null)
    {
        $number = BC::pow($left, $right, $scale);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @test
     */
    public function shouldPowUsingGlobalScale()
    {
        BC::setScale(0);
        self::assertSame('74', BC::pow('4.2', '3'));
        self::assertSame('74.08', BC::pow('4.2', '3', 2));
        BC::setScale(2);
        self::assertSame('74', BC::pow('4.2', '3', 0));
        self::assertSame('74.08', BC::pow('4.2', '3'));
    }

    public function powModProvider()
    {
        return [
            ['4', '5', '2', '7', 0],
            ['-4', '-2', '5', '7', 0],
            ['790', '10', '2147483648', '2047', 0],
            ['790', 1E+1, 2E+8, 2047, 0],
        ];
    }

    /**
     * @test
     * @dataProvider powModProvider
     * @param string $expected
     * @param string $left
     * @param string $right
     * @param string $modulus
     * @param null|int $scale
     */
    public function shouldPowMod($expected, $left, $right, $modulus, $scale)
    {
        $number = BC::powMod($left, $right, $modulus, $scale);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    public function sqrtProvider()
    {
        return [
            ['3', '9', 0],
            ['3.07', '9.444', 2],
            ['43913234134.28826', '1928372132132819737213', 5],
            ['0.31', 9.9999E-2, 2],
        ];
    }

    /**
     * @test
     * @param string $expected
     * @param string $operand
     * @param int $scale
     * @dataProvider sqrtProvider
     */
    public function shouldSqrt($expected, $operand, $scale)
    {
        $number = BC::sqrt($operand, $scale);
        self::assertInternalType('string', $number);
        self::assertSame($expected, $number);
    }

    /**
     * @test
     */
    public function shouldSqrtUsingGlobalScale()
    {
        BC::setScale(0);
        self::assertSame('3', BC::sqrt('9.444'));
        self::assertSame('3.07', BC::sqrt('9.444', 2));
        BC::setScale(2);
        self::assertSame('3', BC::sqrt('9.444', 0));
        self::assertSame('3.07', BC::sqrt('9.444'));
    }
}