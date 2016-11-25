<?php

namespace Fgms\SpecialOffersBundle\EventListener;

class EmailNotificationListener
{
    private $config;
    private $swift;
    private $twig;

    public function __construct(array $config, \Swift_Mailer $swift, \Twig_Environment $twig)
    {
        $this->config = $config;
        $this->swift = $swift;
        $this->twig = $twig;
    }

    private function isEnabled()
    {
        return $this->config['enabled'];
    }

    private function getFrom()
    {
        $from = $this->config['from'];
        if (isset($from['name'])) return [$from['address'] => $from['name']];
        return [$from['address']];
    }

    private function getTo()
    {
        $to = $this->config['to'];
        $retr = [];
        foreach ($to as $addr) {
            if (isset($addr['name'])) $retr[$addr['address']] = $addr['name'];
            else $retr[] = $addr['address'];
        }
        return $retr;
    }

    private function getMessage()
    {
        $msg = new \Swift_Message(null,null,'text/plain','UTF-8');
        $msg->setFrom($this->getFrom());
        $msg->setTo($this->getTo());
        return $msg;
    }

    public function onStart(\Fgms\SpecialOffersBundle\Event\PriceChangeEvent $event)
    {
        if (!$this->isEnabled()) return;
        $msg = $this->getMessage();
        $msg->setSubject('Started');
        $msg->setBody('Special offer started');
        $this->swift->send($msg);
    }

    public function onEnd(\Fgms\SpecialOffersBundle\Event\PriceChangeEvent $event)
    {
        if (!$this->isEnabled()) return;
        $msg = $this->getMessage();
        $msg->setSubject('Ended');
        $msg->setBody('Special offer ended');
        $this->swift->send($msg);
    }
}
