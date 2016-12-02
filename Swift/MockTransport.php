<?php

namespace Fgms\SpecialOffersBundle\Swift;

class MockTransport implements \Swift_Transport
{
    private $msgs = [];

    public function isStarted()
    {
        return true;
    }

    public function start()
    {
    }

    public function stop()
    {
    }

    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->msgs[] = $message;
    }

    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        throw new \LogicException('Unimplemented');
    }

    public function getMessages()
    {
        return $this->msgs;
    }
}
