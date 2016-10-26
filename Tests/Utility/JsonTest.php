<?php

namespace Fgms\SpecialOffersBundle\Tests\Utility;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    public function testEncode()
    {
        $this->assertSame('[]',\Fgms\SpecialOffersBundle\Utility\Json::encode([]));
    }

    public function testDecode()
    {
        $this->assertSame('aoeu',\Fgms\SpecialOffersBundle\Utility\Json::decode('"aoeu"'));
    }

    public function testDecodeFail()
    {
        $this->expectException(\Fgms\SpecialOffersBundle\Exception\JsonException::class);
        \Fgms\SpecialOffersBundle\Utility\Json::decode('aoeu');
    }
}
