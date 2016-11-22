<?php

namespace Fgms\SpecialOffersBundle\Tests\Entity;

class SpecialOfferTest extends \PHPUnit_Framework_TestCase
{
    private $offer;
    private $reflection;

    protected function setUp()
    {
        $this->offer = new \Fgms\SpecialOffersBundle\Entity\SpecialOffer();
        $this->reflection = new \ReflectionClass($this->offer);
    }

    private function getProperty($key)
    {
        $retr = $this->reflection->getProperty($key);
        $retr->setAccessible(true);
        return $retr;
    }

    private function set($key, $value)
    {
        $this->getProperty($key)->setValue($this->offer,$value);
    }

    private function get($key)
    {
        return $this->getProperty($key)->getValue($this->offer);
    }

    public function testDefaultVariantId()
    {
        $arr = $this->offer->getVariantIds();
        $this->assertTrue(is_array($arr));
        $this->assertSame(0,count($arr));
    }

    public function testVariantIds()
    {
        $this->offer->setVariantIds([0,1]);
        $this->assertSame('[0,1]',$this->get('variantIds'));
        $arr = $this->offer->getVariantIds();
        $this->assertTrue(is_array($arr));
        $this->assertSame(2,count($arr));
        $this->assertSame(0,$arr[0]);
        $this->assertSame(1,$arr[1]);
    }

    public function testBadVariantIds()
    {
        $this->set('variantIds','{}');
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\JsonException::class);
        $this->offer->getVariantIds();
    }

    public function testDefaultSlideshow()
    {
        $arr = $this->offer->getSlideshow();
        $this->assertTrue(is_array($arr));
        $this->assertSame(0,count($arr));
    }

    public function testSlideshow()
    {
        $this->offer->setSlideshow([new \stdClass()]);
        $this->assertSame('[{}]',$this->get('slideshow'));
        $arr = $this->offer->getSlideshow();
        $this->assertTrue(is_array($arr));
        $this->assertSame(1,count($arr));
        $o = $arr[0];
        $this->assertTrue(is_object($o));
        $this->assertSame(0,count(get_object_vars($o)));
    }

    public function testBadSlideshow()
    {
        $this->set('slideshow','{}');
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\JsonException::class);
        $this->offer->getSlideshow();
    }

    public function testDefaultTags()
    {
        $arr = $this->offer->getTags();
        $this->assertTrue(is_array($arr));
        $this->assertSame(0,count($arr));
    }

    public function testTags()
    {
        $this->offer->setTags(["aoeu","foo"]);
        $this->assertSame('["aoeu","foo"]',$this->get('tags'));
        $arr = $this->offer->getTags();
        $this->assertTrue(is_array($arr));
        $this->assertSame(2,count($arr));
        $this->assertSame('aoeu',$arr[0]);
        $this->assertSame('foo',$arr[1]);
    }

    public function testBadTags()
    {
        $this->set('tags','{}');
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\JsonException::class);
        $this->offer->getTags();
    }
}
