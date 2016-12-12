<?php

namespace Fgms\SpecialOffersBundle\Tests\EventListener;

class EmailNotificationListenerTest extends \PHPUnit_Framework_TestCase
{
    private $listener;
    private $twig;
    private $swift;
    private $config;
    private $offer;
    private $changes;

    protected function setUp()
    {
        $this->twig = new \Twig_Loader_Array([
            'test.txt.twig' => '{{offer.title}}'
        ]);
        $this->swift = new \Fgms\SpecialOffersBundle\Swift\MockTransport();
        $this->listener = null;
        $this->config = [
            'enabled' => true,
            'from' => [
                'name' => 'Robert A.H. Leahy',
                'address' => 'test@example.org'
            ],
            'to' => [
                [
                    'address' => 'foo@bar.com'
                ]
            ],
            'start_template' => 'test.txt.twig',
            'end_template' => 'test.txt.twig'
        ];
        $this->changes = [];
        $this->offer = new \Fgms\SpecialOffersBundle\Entity\SpecialOffer();
        $this->offer->setTitle('Test');
    }

    private function create()
    {
        $twig = new \Twig_Environment($this->twig);
        $swift = \Swift_Mailer::newInstance($this->swift);
        return $this->listener = new \Fgms\SpecialOffersBundle\EventListener\EmailNotificationListener(
            $this->config,
            $swift,
            $twig
        );
    }

    private function getEvent()
    {
        return new \Fgms\SpecialOffersBundle\Event\PriceChangeEvent($this->offer,$this->changes);
    }

    public function testOnStart()
    {
        $this->create()->onStart($this->getEvent());
        $msgs = $this->swift->getMessages();
        $this->assertCount(1,$msgs);
        $msg = $msgs[0];
        $this->assertSame('Test',$msg->getBody());
        $to = $msg->getTo();
        $this->assertCount(1,$to);
        $this->assertArrayHasKey('foo@bar.com',$to);
        $this->assertNull($to['foo@bar.com']);
        $from = $msg->getFrom();
        $this->assertCount(1,$from);
        $this->assertArrayHasKey('test@example.org',$from);
        $this->assertSame('Robert A.H. Leahy',$from['test@example.org']);
        $this->assertSame('text/plain',$msg->getContentType());
        $this->assertSame('UTF-8',$msg->getCharset());
    }

    public function testOnStartDisabled()
    {
        $this->config['enabled'] = false;
        $this->create()->onStart($this->getEvent());
        $msgs = $this->swift->getMessages();
        $this->assertCount(0,$msgs);
    }

    public function testOnEnd()
    {
        $this->twig->setTemplate('test.html.twig','<html><head></head><body>{{offer.title}} - {{changes|length}}</body></html>');
        $this->config['end_template'] = 'test.html.twig';
        $this->create()->onEnd($this->getEvent());
        $msgs = $this->swift->getMessages();
        $this->assertCount(1,$msgs);
        $msg = $msgs[0];
        $this->assertSame('<html><head></head><body>Test - 0</body></html>',$msg->getBody());
        $to = $msg->getTo();
        $this->assertCount(1,$to);
        $this->assertArrayHasKey('foo@bar.com',$to);
        $this->assertNull($to['foo@bar.com']);
        $from = $msg->getFrom();
        $this->assertCount(1,$from);
        $this->assertArrayHasKey('test@example.org',$from);
        $this->assertSame('Robert A.H. Leahy',$from['test@example.org']);
        $this->assertSame('text/html',$msg->getContentType());
        $this->assertSame('UTF-8',$msg->getCharset());
    }

    public function testOnEndDisabled()
    {
        $this->config['enabled'] = false;
        $this->create()->onEnd($this->getEvent());
        $msgs = $this->swift->getMessages();
        $this->assertCount(0,$msgs);
    }
}
