<?php

namespace Fgms\SpecialOffersBundle\Tests\Form\Type;

class VariantsTypeTest extends \PHPUnit_Framework_TestCase
{
    private $type;

    protected function setUp()
    {
        $this->type = new \Fgms\SpecialOffersBundle\Form\Type\VariantsType();
    }

    public function testTransform()
    {
        $str = $this->type->transform([12,13,14]);
        $this->assertSame('12,13,14',$str);
    }

    public function testTransformNull()
    {
        $str = $this->type->transform(null);
        $this->assertSame('',$str);
    }

    public function testTransformEmpty()
    {
        $str = $this->type->transform([]);
        $this->assertSame('',$str);
    }

    public function testReverseTransform()
    {
        $arr = $this->type->reverseTransform('   13,   14,15');
        $this->assertCount(3,$arr);
        $this->assertSame($arr[0],13);
        $this->assertSame($arr[1],14);
        $this->assertSame($arr[2],15);
    }

    public function testReverseTransformNonString()
    {
        $arr = $this->type->reverseTransform([]);
        $this->assertCount(0,$arr);
    }

    public function testReverseTransformEmpty()
    {
        $arr = $this->type->reverseTransform('');
        $this->assertCount(0,$arr);
    }
}
