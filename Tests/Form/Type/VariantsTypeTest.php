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
        $arr = $this->type->transform([12,13,14]);
        $this->assertCount(3,$arr);
        $this->assertSame(12,$arr[0]);
        $this->assertSame(13,$arr[1]);
        $this->assertSame(14,$arr[2]);
    }

    public function testReverseTransform()
    {
        $arr = $this->type->reverseTransform(['13','14','15']);
        $this->assertCount(3,$arr);
        $this->assertSame($arr[0],13);
        $this->assertSame($arr[1],14);
        $this->assertSame($arr[2],15);
    }

    public function testReverseTransformEmpty()
    {
        $arr = $this->type->reverseTransform([]);
        $this->assertCount(0,$arr);
    }
}
