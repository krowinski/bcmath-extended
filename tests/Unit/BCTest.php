<?php

namespace BCMathExtended\Tests\Unit;

use BCMathExtended\BC;

/**
 * Class BcTest
 */
class BCTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldConvertScientificNotationToString()
    {
        self::assertSame('0', BC::convertScientificNotationToString('-0'));
        self::assertSame('0', BC::convertScientificNotationToString(''));
        self::assertSame('666', BC::convertScientificNotationToString('666'));
        self::assertSame('-666', BC::convertScientificNotationToString('-666'));
        self::assertSame('99999999999999999999999999999999999.000000000000000000000', BC::convertScientificNotationToString('99999999999999999999999999999999999.000000000000000000000'));
        self::assertSame('99999999999999999999999999999999999.999999999999999999999', BC::convertScientificNotationToString('99999999999999999999999999999999999.999999999999999999999'));
        self::assertSame('1000000000000000000000000000000', BC::convertScientificNotationToString(1.0E+30));
        self::assertSame('-1540000000000000', BC::convertScientificNotationToString(-1.54E+15));
        self::assertSame('1540000000000000', BC::convertScientificNotationToString(1.54E+15));
        self::assertSame('602200000000000000000000', BC::convertScientificNotationToString('6.022E+23'));
        self::assertSame('602200000000000000000000', BC::convertScientificNotationToString('6.022e+23'));
        self::assertSame('-602200000000000000000000', BC::convertScientificNotationToString('-6.022e+23'));
        self::assertSame('-602200000000000000000000', BC::convertScientificNotationToString('-6.022e+23'));

        self::assertSame('0.00000000001', BC::convertScientificNotationToString(1.0E-11));
        self::assertSame('0.0000051', BC::convertScientificNotationToString(5.1E-6));
        self::assertSame('0.02', BC::convertScientificNotationToString(2E-2));
        self::assertSame('0.0021', BC::convertScientificNotationToString(2.1E-3));
        self::assertSame('0.00000003', BC::convertScientificNotationToString(3E-8));
        self::assertSame('0.00000003', BC::convertScientificNotationToString(3E-8));
        self::assertSame('0.000000657', BC::convertScientificNotationToString(6.57E-7));
    }

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

        self::assertSame('0', BC::ceil('-0'));
        self::assertSame('0', BC::ceil(''));
        self::assertSame('0', BC::ceil(null));
        self::assertSame('20000', BC::ceil('2/0000'));
        self::assertSame('-60000', BC::ceil('-6/0000'));
        self::assertSame('1000000000000000000000000000000', BC::ceil('+1/000000000000000000000000000000'));

        self::assertSame('99999999999999999999999999999999999', BC::ceil('99999999999999999999999999999999999.000000000000000000000'));
        self::assertSame('100000000000000000000000000000000000', BC::ceil('99999999999999999999999999999999999.999999999999999999999'));

        self::assertSame('0', BC::ceil('0-'));

        self::assertSame('100000000000000000000000000000000000', BC::ceil(1.0E+35));
        self::assertSame('-100000000000000000000000000000000000', BC::ceil(-1.0E+35));
        self::assertSame('1', BC::ceil(3E-8));
        self::assertSame('1', BC::ceil(1.0E-11));
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

        self::assertSame('0', BC::floor('-0'));
        self::assertSame('0', BC::floor(''));
        self::assertSame('0', BC::floor(null));
        self::assertSame('20000', BC::floor('2/0000'));
        self::assertSame('-60000', BC::floor('-6/0000'));
        self::assertSame('1000000000000000000000000000000', BC::floor('+1/000000000000000000000000000000'));

        self::assertSame('99999999999999999999999999999999999', BC::floor('99999999999999999999999999999999999.000000000000000000000'));
        self::assertSame('99999999999999999999999999999999999', BC::floor('99999999999999999999999999999999999.999999999999999999999'));

        self::assertSame('0', BC::floor('0-'));

        self::assertSame('100000000000000000000000000000000000', BC::floor(1.0E+35));
        self::assertSame('-100000000000000000000000000000000000', BC::floor(-1.0E+35));
        self::assertSame('0', BC::floor(3E-8));
        self::assertSame('0', BC::floor(1.0E-11));
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
        self::assertSame('9999999999999999999999999999999999999999999999999999999', BC::abs('-9999999999999999999999999999999999999999999999999999999'));

        self::assertSame('0', BC::abs('-0'));
        self::assertSame('0', BC::abs(''));
        self::assertSame('0', BC::abs(null));
        self::assertSame('20000', BC::abs('2/0000'));
        self::assertSame('60000', BC::abs('-6/0000'));
        self::assertSame('1000000000000000000000000000000', BC::abs('+1/000000000000000000000000000000'));
        self::assertSame('0', BC::abs('0-'));

        self::assertSame('100000000000000000000000000000000000', BC::abs(1.0E+35));
        self::assertSame('100000000000000000000000000000000000', BC::abs(-1.0E+35));
        self::assertSame('0.0000051', BC::abs(-5.1E-6));
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

        self::assertSame('0', BC::round('-0'));
        self::assertSame('0', BC::round(''));
        self::assertSame('0', BC::round(null));
        self::assertSame('20000', BC::round('2/0000'));
        self::assertSame('-60000', BC::round('-6/0000'));
        self::assertSame('1000000000000000000000000000000', BC::round('+1/000000000000000000000000000000'));
        self::assertSame('0', BC::round('0-'));

        self::assertSame('100000000000000000000000000000000000', BC::round(1.0E+35));
        self::assertSame('-100000000000000000000000000000000000', BC::round(-1.0E+35));
        self::assertSame('0', BC::round(3E-8));
        self::assertSame('0', BC::round(1.0E-11));
        self::assertSame('-0.0006', BC::round(-5.6E-4, 4));
        self::assertSame('0.0000000010', BC::round(9.9999E-10, 10));
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
        self::assertSame('3', BC::max(1, 2, 3));
        self::assertSame('6', BC::max(6, 3, 2));
        self::assertSame('999', BC::max(100, 999, 5));

        self::assertSame('677', BC::max(array(3, 5, 677)));
        self::assertSame('-3', BC::max(array(-3, -5, -677)));

        self::assertSame('999999999999999999999999999999999999999999', BC::max('432423432423423423423423432432423423423', '999999999999999999999999999999999999999999', '321312312423435657'));

        self::assertSame('0.00000000099999',  BC::max(9.9999E-10, -5.6E-4));
    }

    /**
     * @test
     */
    public function shouldMin()
    {
        self::assertSame('1', BC::min(1, 2, 3));
        self::assertSame('2', BC::min(6, 3, 2));
        self::assertSame('5', BC::min(100, 999, 5));

        BC::setScale(2);
        self::assertSame('7.20', BC::min('7.30', '7.20'));

        self::assertSame('3', BC::min(array(3, 5, 677)));
        self::assertSame('-677', BC::min(array(-3, -5, -677)));

        self::assertSame('321312312423435657', BC::min('432423432423423423423423432432423423423', '999999999999999999999999999999999999999999', '321312312423435657'));

        self::assertSame('-0.00056',  BC::min(9.9999E-10, -5.6E-4));
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

    /**
     * @test
     */
    public function shouldRoundUp()
    {
        self::assertSame('663', BC::roundUp(662.79));
        self::assertSame('662.8', BC::roundUp(662.79, 1));
        self::assertSame('60', BC::roundUp(54.1, -1));
        self::assertSame('60', BC::roundUp(55.1, -1));
        self::assertSame('-23.6', BC::roundUp(-23.62, 1));
        self::assertSame('4', BC::roundUp(3.2));
        self::assertSame('77', BC::roundUp(76.9));
        self::assertSame('3.142', BC::roundUp(3.14159, 3));
        self::assertSame('-3.1', BC::roundUp(-3.14159, 1));
        self::assertSame('31500', BC::roundUp(31415.92654, -2));
        self::assertSame('31420', BC::roundUp(31415.92654, -1));
        self::assertSame('0.0119', BC::roundUp(0.0119, 4));

        self::assertSame('0', BC::roundUp('-0'));
        self::assertSame('0', BC::roundUp(''));
        self::assertSame('0', BC::roundUp(null));
        self::assertSame('0', BC::roundUp('0-'));

        self::assertSame('1',  BC::roundUp(9.9999E-10));
    }

    /**
     * @test
     */
    public function shouldRoundDown()
    {
        self::assertSame('662', BC::roundDown(662.79));
        self::assertSame('662.7', BC::roundDown(662.79, 1));
        self::assertSame('50', BC::roundDown(54.1, -1));
        self::assertSame('50', BC::roundDown(55.1, -1));
        self::assertSame('-23.7', BC::roundDown(-23.62, 1));
        self::assertSame('3', BC::roundDown(3.2));
        self::assertSame('76', BC::roundDown(76.9));
        self::assertSame('3.141', BC::roundDown(3.14159, 3));
        self::assertSame('-3.2', BC::roundDown(-3.14159, 1));
        self::assertSame('31400', BC::roundDown(31415.92654, -2));
        self::assertSame('31410', BC::roundDown(31415.92654, -1));
        self::assertSame('0.0119', BC::roundDown(0.0119, 4));

        self::assertSame('0', BC::roundDown('-0'));
        self::assertSame('0', BC::roundDown(''));
        self::assertSame('0', BC::roundDown(null));
        self::assertSame('0', BC::roundDown('0-'));

        self::assertSame('0',  BC::roundDown(9.9999E-10));
    }

    /**
     * @test
     */
    public function shouldAdd()
    {
        BC::setScale(0);
        self::assertSame('3', BC::add('1', '2'));

        BC::setScale(2);
        self::assertSame('2.05', BC::add('1', '1.05'));

        self::assertSame('4.0000', BC::add('-1', '5', 4));
        self::assertSame('8728932003911564969352217864684.00', BC::add('1928372132132819737213', '8728932001983192837219398127471', 2));

        self::assertSame('-0.00055999', BC::add(9.9999E-10, -5.6E-4, 8));
    }

    /**
     * @test
     */
    public function shouldSub()
    {
        BC::setScale(0);
        self::assertSame('-1', BC::sub('1', '2'));

        BC::setScale(2);
        self::assertSame('-1.50', BC::sub('1', '2.5'));

        self::assertSame('-6.0000', BC::sub('-1', '5', 4));
        self::assertSame('8728932000054820705086578390258.00', BC::sub('8728932001983192837219398127471', '1928372132132819737213', 2));

        self::assertSame('0.00056000', BC::sub(9.9999E-10, -5.6E-4, 8));
    }

    /**
     * @test
     */
    public function shouldComp()
    {
        BC::setScale(1);
        self::assertSame(BC::COMPARE_RIGHT_GRATER, BC::comp('100.0', '100.5'));

        self::assertSame(BC::COMPARE_RIGHT_GRATER, BC::comp('-1', '5', 4));
        self::assertSame(BC::COMPARE_RIGHT_GRATER, BC::comp('1928372132132819737213', '8728932001983192837219398127471'));
        self::assertSame(BC::COMPARE_EQUAL, BC::comp('1.00000000000000000001', '1', 2));
        self::assertSame(BC::COMPARE_LEFT_GRATER, BC::comp('97321', '2321'));
    }

    /**
     * @test
     */
    public function shouldGetScale()
    {
        BC::setScale(10);
        self::assertSame(10, BC::getScale());

        BC::setScale(25);
        self::assertSame(25, BC::getScale());

        BC::setScale(0);
        self::assertSame(0, BC::getScale());
    }

    /**
     * @test
     */
    public function shouldDiv()
    {
        BC::setScale(0);
        self::assertSame('0', BC::div('1', '2'));
        BC::setScale(2);
        self::assertSame('0.50', BC::div('1', '2'));

        self::assertSame('0.50', BC::div('1', '2', 2));
        self::assertSame('-0.2000', BC::div('-1', '5', 4));
        self::assertSame('4526580661.75', BC::div('8728932001983192837219398127471', '1928372132132819737213', 2));

        self::assertSame(9.9999E-11, (float)BC::div(9.9999E-10, 10, 15));
        self::assertSame('0.000000000099999', BC::div(9.9999E-10, 10, 15));
    }

    /**
     * @test
     */
    public function shouldMod()
    {
        self::assertSame('1', BC::mod('11', '2'));
        self::assertSame('-1', BC::mod('-1', '5'));
        self::assertSame('1459434331351930289678', BC::mod('8728932001983192837219398127471', '1928372132132819737213'));

        self::assertSame('0', BC::mod(9.9999E-10, 1));
    }

    /**
     * @test
     */
    public function shouldFmod()
    {
        self::assertSame('0.8', BC::fmod('10', '9.2', 1));
        self::assertSame('0.0', BC::fmod('20', '4.0', 1));
        self::assertSame('0.0', BC::fmod('10.5', '3.5', 1));
        self::assertSame('0.3', BC::fmod('10.2', '3.3', 1));

        self::assertSame('-0.000559999', BC::fmod(9.9999E-10, -5.6E-4, 9));
    }

    /**
     * @test
     */
    public function shouldMul()
    {
        self::assertSame('2', BC::mul('1', '2'));
        self::assertSame('-15', BC::mul('-3', '5'));
        self::assertSame('12193263111263526900', BC::mul('1234567890', '9876543210'));
        self::assertSame('3.75', BC::mul('2.5', '1.5', 2));
        self::assertSame('3.97', BC::mul('2.555', '1.555', 2));

        self::assertSame('-0.005599944', BC::mul(9.9999E-2, -5.6E-2, 9));
    }

    /**
     * @test
     */
    public function shouldPow()
    {
        BC::setScale(0);
        self::assertSame('1', BC::pow('1', '2'));

        BC::setScale(2);
        self::assertSame('74.08', BC::pow('4.2', '3'));

        self::assertSame('-32', BC::pow('-2', '5', 4));
        self::assertSame('18446744073709551616', BC::pow('2', '64'));
        self::assertSame('-108.88', BC::pow('-2.555', '5', 2));

        self::assertSame('63998080023999840000.599998800', BC::pow(19.9999E+2, 6, 9));
    }

    /**
     * @test
     */
    public function shouldPowMod()
    {
        BC::setScale(0);
        self::assertSame('4', BC::powMod('5', '2', '7'));
        self::assertSame('-4', BC::powMod('-2', '5', '7'));
        self::assertSame('790', BC::powMod('10', '2147483648', '2047'));

        self::assertFalse(BC::powMod(9.9999E-2, -5.6E-2, 9));
    }

    /**
     * @test
     */
    public function shouldSqrt()
    {
        self::assertSame('3', BC::sqrt('9'));
        self::assertSame('3.07', BC::sqrt('9.444', 2));
        self::assertSame('43913234134.28826', BC::sqrt('1928372132132819737213', 5));

        self::assertSame('0.31', BC::sqrt(9.9999E-2, 2));
    }
}