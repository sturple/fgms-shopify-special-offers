<?php

namespace Fgms\SpecialOffersBundle\Shopify;

/**
 * Allows interaction with the Shopify API via HTTP.
 */
class Client implements ClientInterface
{
    private $api_key;
    private $secret;
    private $store_name;
    private $client;
    private $token = null;

    /**
     * Creates a new Client.
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

    /**
     * Retrieves an API token.
     *
     * The API token will be set on this object
     * and returned, there is no need to additionally
     * call @ref setToken.
     *
     * @param string code
     *
     * @return Object
     */
    public function getToken($code)
    {
        $request = new \GuzzleHttp\Psr7\Request(
            'POST',
            $this->getUrl('/admin/oauth/access_token'),
            ['Content-Type' => 'application/json;charset=utf-8'],
            \Fgms\SpecialOffersBundle\Utility\Json::encode([
                'client_id' => $this->api_key,
                'client_secret' => $this->secret,
                'code' => $code
            ])
        );
        $obj = $this->execute($request);
        $this->setToken($obj->getString('access_token'));
        return $obj;
    }

    private function getUrl($endpoint)
    {
        return sprintf(
            'https://%s.myshopify.com/%s.json',
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
        if (is_null($this->token)) throw new \LogicException('No access token');
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
            \Fgms\SpecialOffersBundle\Utility\Json::encode($args)
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
        return ObjectWrapper::create($response->getBody());
    }

    private function raiseError(\GuzzleHttp\Exception\BadResponseException $e)
    {
        if (!$e->hasResponse()) throw new \LogicException('BadResponseException with no Response',0,$e);
        $response = $e->getResponse();
        $obj = $this->decodeResponse($response);
        throw new Exception\ClientException(
            $obj->getString('errors'),
            0,
            $e
        );
    }

    private function execute(\GuzzleHttp\Psr7\Request $request)
    {
        try {
            $response = $this->client->send($request);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $this->raiseError($e);
        }
        return $this->decodeResponse($response);
    }

    public function call($method, $endpoint, array $args = [])
    {
        $request = $this->createRequest($method,$endpoint,$args);
        return $this->execute($request);
    }

    private function getHmacValue($str)
    {
        //  "The characters & and % are replaced with %26 and %25 respectively
        //  in keys and values."
        $str = preg_replace('/&/u','%26',$str);
        return preg_replace('/%/u','%25',$str);
    }

    private function getHmacKey($str)
    {
        $str = $this->getHmacValue($str);
        //  "Additionally the = character is replaced with %3D in keys."
        return preg_replace('/=/u','%3D',$str);
    }

    public function verify(\Symfony\Component\HttpFoundation\Request $request)
    {
        $incoming = $request->query->get('hmac');
        if (!is_string($incoming)) return false;
        $material = [];
        foreach ($request->query as $key => $value) {
            //  "The hmac entry is removed from the map [...]"
            if ($key === 'hmac') continue;
            if (!is_string($value)) return false;
            //  "Each key is concatenated with its value, separated by an = character,
            //  to create a list of strings."
            $material[] = $this->getHmacKey($key) . '=' . $this->getHmacValue($value);
        }
        //  "The list of key-value pairs is sorted lexicographically [...]"
        usort($material,function ($a, $b) { return strcmp($a,$b);   });
        //  "[...] and concatenated together with & to create a single string [...]"
        $str = implode('&',$material);
        //  "Lastly, this string processed through an HMAC-SHA256 using the Shared
        //  Secret as the key."
        $hmac = hash_hmac('sha256',$str,$this->secret);
        return $hmac === $incoming;
    }
}
