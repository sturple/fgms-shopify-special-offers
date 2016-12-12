<?php

namespace Fgms\SpecialOffersBundle\Tests\Form\Type;

class DiscountTypeTest extends \PHPUnit_Framework_TestCase
{
    private $type;

    protected function setUp()
    {
        $this->type = new \Fgms\SpecialOffersBundle\Form\Type\DiscountType();
    }

    public function testTransformCents()
    {
        $result = $this->type->transform([
            'cents' => 800,
            'percent' => null
        ]);
        $this->assertCount(2,$result);
        $this->assertSame('8.00',$result['value']);
        $this->assertSame('$',$result['type']);
    }

    public function testTransformPercentNoDecimal()
    {
        $result = $this->type->transform([
            'percent' => 80.0,
            'cents' => null
        ]);
        $this->assertCount(2,$result);
        $this->assertSame('80',$result['value']);
        $this->assertSame('%',$result['type']);
    }

    public function testTransformPercentDecimal()
    {
        $result = $this->type->transform([
            'percent' => 80.5,
            'cents' => null
        ]);
        $this->assertCount(2,$result);
        $this->assertSame('80.5',$result['value']);
        $this->assertSame('%',$result['type']);
    }

    public function testTransformBoth()
    {
        $this->expectException(\Symfony\Component\Form\Exception\TransformationFailedException::class);
        $this->type->transform(['percent' => 80,'cents' => 800]);
    }

    public function testTransformNeither()
    {
        $this->expectException(\Symfony\Component\Form\Exception\TransformationFailedException::class);
        $this->type->transform(['percent' => null,'cents' => null]);
    }

    public function testTransformBadCents()
    {
        $this->expectException(\Symfony\Component\Form\Exception\TransformationFailedException::class);
        $this->type->transform(['cents' => 'foo','percent' => null]);
    }

    public function testTransformBadPercent()
    {
        $this->expectException(\Symfony\Component\Form\Exception\TransformationFailedException::class);
        $this->type->transform(['percent' => 'bar','cents' => null]);
    }

    public function testReverseTransformCents()
    {
        $result = $this->type->reverseTransform([
            'type' => '$',
            'value' => '8.00'
        ]);
        $this->assertCount(2,$result);
        $this->assertNull($result['percent']);
        $this->assertSame(800,$result['cents']);
    }

    public function testReverseTransformNegativeCents()
    {
        $this->expectException(\Symfony\Component\Form\Exception\TransformationFailedException::class);
        $this->type->reverseTransform([
            'type' => '$',
            'value' => '-2'
        ]);
    }

    public function testReverseTransformPercent()
    {
        $result = $this->type->reverseTransform([
            'type' => '%',
            'value' => '90'
        ]);
        $this->assertCount(2,$result);
        $this->assertNull($result['cents']);
        $this->assertSame(90.0,$result['percent']);
    }

    public function testReverseTransformNegativePercent()
    {
        $this->expectException(\Symfony\Component\Form\Exception\TransformationFailedException::class);
        $this->type->reverseTransform([
            'type' => '%',
            'value' => '-1'
        ]);
    }

    public function testReverseTransformUnrecognizedType()
    {
        $this->expectException(\Symfony\Component\Form\Exception\TransformationFailedException::class);
        $this->type->reverseTransform([
            'type' => 'foo',
            'value' => '2'
        ]);
    }
}
