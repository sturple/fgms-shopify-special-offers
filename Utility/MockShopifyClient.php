<?php

namespace Fgms\SpecialOffersBundle\Utility;

class MockShopifyClient implements ShopifyInterface
{
    private $requests = [];
    private $responses = [];

    public function addResponse($obj)
    {
        $json = Json::encode($obj);
        $this->responses[] = ShopifyObject::create($json);
    }

    public function getRequests()
    {
        return $this->requests;
    }

    public function call($method, $endpoint, array $args = [])
    {
        if (count($this->responses) === 0) throw new \LogicException('No response');
        $this->requests[] = (object)[
            'method' => $method,
            'endpoint' => $endpoint,
            'args' => $args
        ];
        return array_shift($this->responses);
    }
}
