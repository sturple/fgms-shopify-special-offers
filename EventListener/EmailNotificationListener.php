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

    private function getMimeType($template)
    {
        if (preg_match('/\\.html\\.twig$/u',$template)) return 'text/html';
        return 'text/plain';
    }

    private function getMessage($subject, $template, array $ctx)
    {
        $body = $this->twig->render($template,$ctx);
        $mime = $this->getMimeType($template);
        $msg = new \Swift_Message($subject,$body,$mime,'UTF-8');
        $msg->setFrom($this->getFrom())
            ->setTo($this->getTo());
        return $msg;
    }

    private function getContext(\Fgms\SpecialOffersBundle\Event\PriceChangeEvent $event)
    {
        return [
            'changes' => $event->getPriceChanges(),
            'offer' => $event->getSpecialOffer()
        ];
    }

    public function onStart(\Fgms\SpecialOffersBundle\Event\PriceChangeEvent $event)
    {
        if (!$this->isEnabled()) return;
        $offer = $event->getSpecialOffer();
        $subject = sprintf('Special Offer %s Started',$offer->getTitle());
        $ctx = $this->getContext($event);
        $msg = $this->getMessage($subject,$this->config['start_template'],$ctx);
        $this->swift->send($msg);
    }

    public function onEnd(\Fgms\SpecialOffersBundle\Event\PriceChangeEvent $event)
    {
        if (!$this->isEnabled()) return;
        $offer = $event->getSpecialOffer();
        $subject = sprintf('Special Offer %s Ended',$offer->getTitle());
        $ctx = $this->getContext($event);
        $msg = $this->getMessage($subject,$this->config['end_template'],$ctx);
        $this->swift->send($msg);
    }
}
