<?php

namespace Fgms\SpecialOffersBundle\Tests\Utility;

class ConvertTest extends \PHPUnit_Framework_TestCase
{
    public function testToIntegerSuccess()
    {
        $this->assertSame(5,\Fgms\SpecialOffersBundle\Utility\Convert::toInteger('5'));
    }

    public function testToIntegerFailure()
    {
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\ConvertException::class);
        \Fgms\SpecialOffersBundle\Utility\Convert::toInteger('foo');
    }

    public function testToCentsNoDecimal()
    {
        $this->assertSame(500,\Fgms\SpecialOffersBundle\Utility\Convert::toCents('5'));
    }

    public function testToCentsDecimal()
    {
        $this->assertSame(500,\Fgms\SpecialOffersBundle\Utility\Convert::toCents('5.'));
    }

    public function testToCentsOneDecimal()
    {
        $this->assertSame(510,\Fgms\SpecialOffersBundle\Utility\Convert::toCents('5.1'));
    }

    public function testToCentsTwoDecimals()
    {
        $this->assertSame(512,\Fgms\SpecialOffersBundle\Utility\Convert::toCents('5.12'));
    }

    public function testToCentsTooManyDecimals()
    {
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\ConvertException::class);
        \Fgms\SpecialOffersBundle\Utility\Convert::toCents('5.123');
    }

    public function testToCentsTwoDecimalPoints()
    {
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\ConvertException::class);
        \Fgms\SpecialOffersBundle\Utility\Convert::toCents('5.6.12');
    }

    public function testToCentsNotANumber()
    {
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\ConvertException::class);
        \Fgms\SpecialOffersBundle\Utility\Convert::toCents('foo');
    }

    public function testToFloatSuccess()
    {
        $this->assertSame(5.5,\Fgms\SpecialOffersBundle\Utility\Convert::toFloat('5.5'));
        $this->assertSame(5.0,\Fgms\SpecialOffersBundle\Utility\Convert::toFloat('5'));
    }

    public function testToFloatFailure()
    {
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\ConvertException::class);
        \Fgms\SpecialOffersBundle\Utility\Convert::toFloat('aoeu');
    }
}
