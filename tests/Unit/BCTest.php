<?php

namespace Unit;

use BCMathExtended\BC;

/**
 * Class BcTest
 */
class BcTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCeil()
    {
        self::assertSame('0', BC::ceil(-0));
        self::assertSame('-1', BC::ceil(-1));
        self::assertSame('-1', BC::ceil(-1.5));
        self::assertSame('-1', BC::ceil(-1.8));
        self::assertSame('-2', BC::ceil(-2.7));
        self::assertSame('0', BC::ceil(0));
        self::assertSame('1', BC::ceil(0.5));
        self::assertSame('1', BC::ceil(1));
        self::assertSame('2', BC::ceil(1.5));
        self::assertSame('2', BC::ceil(1.8));
        self::assertSame('3', BC::ceil(2.7));

        self::assertSame('0', BC::ceil(''));
        self::assertSame('0', BC::ceil(null));

        self::assertSame('99999999999999999999999999999999999', BC::ceil('99999999999999999999999999999999999.000000000000000000000'));
    }

    /**
     * @test
     */
    public function shouldFloor()
    {
        self::assertSame('0', BC::floor(-0));
        self::assertSame('-1', BC::floor(-0.5));
        self::assertSame('-1', BC::floor(-1));
        self::assertSame('-2', BC::floor(-1.5));
        self::assertSame('-2', BC::floor(-1.8));
        self::assertSame('-3', BC::floor(-2.7));

        self::assertSame('0', BC::floor(0));
        self::assertSame('0', BC::floor(0.5));
        self::assertSame('1', BC::floor(1));
        self::assertSame('1', BC::floor(1.5));
        self::assertSame('1', BC::floor(1.8));
        self::assertSame('2', BC::floor(2.7));

        self::assertSame('0', BC::floor(''));
        self::assertSame('0', BC::floor(null));

        self::assertSame('99999999999999999999999999999999999', BC::floor('99999999999999999999999999999999999.000000000000000000000'));
    }

    /**
     * @test
     */
    public function shouldAbs()
    {
        self::assertSame('1', BC::abs(-1));
        self::assertSame('1.5', BC::abs(-1.5));
        self::assertSame('1', BC::abs('-1'));
        self::assertSame('1.5', BC::abs('-1.5'));

        self::assertSame('0', BC::abs(''));
        self::assertSame('0', BC::abs(null));
    }

    /**
     * @test
     */
    public function shouldRound()
    {
        self::assertSame('3', BC::round('3.4'));
        self::assertSame('4', BC::round('3.5'));
        self::assertSame('4', BC::round('3.6'));
        self::assertSame('2', BC::round('1.95583'));
        self::assertSame('2', BC::round('1.95583'));
        self::assertSame('1.96', BC::round('1.95583', 2));
        self::assertSame('1.956', BC::round('1.95583', 3));
        self::assertSame('1.9558', BC::round('1.95583', 4));
        self::assertSame('1.95583', BC::round('1.95583', 5));
        self::assertSame('1241757', BC::round('1241757'));
        self::assertSame('1241757', BC::round('1241757', 5));
        self::assertSame('-3', BC::round('-3.4'));
        self::assertSame('-4', BC::round('-3.5'));
        self::assertSame('-4', BC::round('-3.6'));
        self::assertSame('123456.745671', BC::round('123456.7456713', 6));
        self::assertSame('1', BC::round('1.11'));
        self::assertSame('1.11', BC::round('1.11', 2));
        self::assertSame('0.1666666666667', BC::round('0.1666666666666665', 13));
        self::assertSame('0', BC::round('0.1666666666666665', 0.13));
        self::assertSame('10', BC::round('9.999'));
        self::assertSame('10.00', BC::round('9.999', 2));
        self::assertSame('0.01', BC::round('0.005', 2));
        self::assertSame('0.02', BC::round('0.015', 2));
        self::assertSame('0.03', BC::round('0.025', 2));
        self::assertSame('0.04', BC::round('0.035', 2));
        self::assertSame('0.05', BC::round('0.045', 2));
        self::assertSame('0.06', BC::round('0.055', 2));
        self::assertSame('0.07', BC::round('0.065', 2));
        self::assertSame('0.08', BC::round('0.075', 2));
        self::assertSame('0.09', BC::round('0.085', 2));

        self::assertSame('77777777777777777777777777777', BC::round('77777777777777777777777777777.1'));
        self::assertSame('100000000000000000000000000000000000', BC::round('99999999999999999999999999999999999.99999999999999999999999999999999991'));
        self::assertSame('99999999999999999999999999999999999', BC::round('99999999999999999999999999999999999.00000000000000000000000000000000001'));

        self::assertSame('99999999999999999999999999999999999', BC::round('99999999999999999999999999999999999.000000000000000000000'));

        self::assertSame('0', BC::round(''));
        self::assertSame('0', BC::round(null));
    }

    /**
     * @test
     */
    public function shouldRand()
    {
        self::assertInternalType('string', BC::rand(1, 3));
        self::assertInternalType('string', BC::rand('432423432423423423423423432432423423423', '999999999999999999999999999999999999999999'));

        $left = '432423432423423423423423432432423423423';
        $right = '999999999999999999999999999999999999999999';
        $rand = BC::rand($left, $right);
        self::assertTrue($rand >= $left);
        self::assertTrue($rand <= $right);
    }

    /**
     * @test
     */
    public function shouldMax()
    {
        self::assertSame(3, BC::max(1, 2, 3));
        self::assertSame(6, BC::max(6, 3, 2));
        self::assertSame(999, BC::max(100, 999, 5));

        self::assertSame('999999999999999999999999999999999999999999', BC::max('432423432423423423423423432432423423423', '999999999999999999999999999999999999999999', '321312312423435657'));
    }

    /**
     * @test
     */
    public function shouldMin()
    {
        self::assertSame(1, BC::min(1, 2, 3));
        self::assertSame(2, BC::min(6, 3, 2));
        self::assertSame(5, BC::min(100, 999, 5));

        self::assertSame('321312312423435657', BC::min('432423432423423423423423432432423423423', '999999999999999999999999999999999999999999', '321312312423435657'));
    }

    /**
     * @test
     */
    public function shouldSetScale()
    {
        BC::setScale(50);
        self::assertSame('3.00000000000000000000000000000000000000000000000000', bcadd('1', '2'));

        BC::setScale(null);
        self::assertSame('3', bcadd('1', '2'));

        BC::setScale(13);
        self::assertSame('3.0000000000000', bcadd('1', '2'));
    }
}