<?php

namespace Fgms\SpecialOffersBundle\Utility;

/**
 * Allows interaction with the Shopify API via HTTP.
 */
class ShopifyClient implements ShopifyInterface
{
    private $api_key;
    private $secret;
    private $store_name;
    private $client;
    private $token = null;

    /**
     * Creates a new ShopifyClient.
     *
     * @param string $api_key
     * @param string $secret
     * @param string $store_name
     * @param Client|null $client
     */
    public function __construct($api_key, $secret, $store_name, \GuzzleHttp\Client $client = null)
    {
        $this->api_key = $api_key;
        $this->secret = $secret;
        $this->store_name = $store_name;
        if (is_null($client)) $client = new \GuzzleHttp\Client();
        $this->client = $client;
    }

    /**
     * Sets the API token.
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    private function getToken()
    {
        if (!is_null($this->token)) return;
        throw new \LogicException('getToken logic not implemented, please use setToken');
    }

    private function getUrl($endpoint)
    {
        return sprintf(
            'https://%s.myshopify.com/%s',
            $this->store_name,
            preg_replace('/^\\//u','',$endpoint)
        );
    }

    private function encode(array $args)
    {
        $retr = '';
        foreach ($args as $key => $value) {
            if ($retr !== '') $retr .= '&';
            $retr .= sprintf(
                '%s=%s',
                rawurlencode($key),
                rawurlencode($value)
            );
        }
        return $retr;
    }

    private function getHeaders(array $args = [])
    {
        $this->getToken();
        return array_merge(
            ['X-Shopify-Access-Token' => $this->token],
            $args
        );
    }

    private function createRequestImpl($method, $url, array $headers = [], $body = null)
    {
        return new \GuzzleHttp\Psr7\Request(
            $method,
            $url,
            $this->getHeaders($headers),
            $body
        );
    }

    private function createBodyRequest($method, $endpoint, array $args)
    {
        return $this->createRequestImpl(
            $method,
            $this->getUrl($endpoint),
            ['Content-Type' => 'application/json;charset=utf-8'],
            Json::encode($args)
        );
    }

    private function createQueryStringRequest($method, $endpoint, array $args)
    {
        return $this->createRequestImpl(
            $method,
            $this->getUrl($endpoint) . '?' . $this->encode($args)
        );
    }

    private function createRequest($method, $endpoint, array $args)
    {
        if (($method === 'GET') || ($method === 'DELETE')) return $this->createQueryStringRequest($method,$endpoint,$args);
        return $this->createBodyRequest($method,$endpoint,$args);
    }

    private function decodeResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        return ShopifyObject::create($response->getBody());
    }

    private function raiseError(\GuzzleHttp\Exception\BadResponseException $e)
    {
        if (!$e->hasResponse()) throw new \LogicException('BadResponseException with no Response',0,$e);
        $response = $e->getResponse();
        $obj = $this->decodeResponse($response);
        throw new \Fgms\SpecialOffersBundle\Exception\ShopifyException(
            $obj->getString('errors'),
            0,
            $e
        );
    }

    public function call($method, $endpoint, array $args = [])
    {
        $request = $this->createRequest($method,$endpoint,$args);
        try {
            $response = $this->client->send($request);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $this->raiseError($e);
        }
        return ShopifyObject::create($response->getBody());
    }
}
