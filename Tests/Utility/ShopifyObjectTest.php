<?php

namespace Fgms\SpecialOffersBundle\Tests\Utility;

class ShopifyObjectTest extends \PHPUnit_Framework_TestCase
{
    private function expectThrows()
    {
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\ShopifyException::class);
    }

    private function create($str = '{}')
    {
        return \Fgms\SpecialOffersBundle\Utility\ShopifyObject::create($str);
    }

    public function testDecode()
    {
        $this->expectThrows();
        $this->create('aoeu');
    }

    public function testDecodeBadRoot()
    {
        $this->expectThrows();
        $this->create('"foo"');
    }

    public function testGetString()
    {
        $obj = $this->create('{"test":"foo"}');
        $str = $obj->getString('test');
        $this->assertSame('foo',$str);
    }

    public function testGetStringEmpty()
    {
        $obj = $this->create();
        $this->expectThrows();
        $obj->getString('test');
    }

    public function testGetStringMismatch()
    {
        $obj = $this->create('{"test":5}');
        $this->expectThrows();
        $obj->getString('test');
    }

    public function testGetOptionalString()
    {
        $obj = $this->create('{"foo":"bar"}');
        $str = $obj->getOptionalString('foo');
        $this->assertSame('bar',$str);
    }

    public function testGetOptionalStringEmpty()
    {
        $obj = $this->create();
        $str = $obj->getOptionalString('bar');
        $this->assertNull($str);
    }

    public function testGetOptionalStringMismatch()
    {
        $obj = $this->create('{"quux":17.2}');
        $this->expectThrows();
        $obj->getOptionalString('quux');
    }
}
