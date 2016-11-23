<?php

namespace Fgms\SpecialOffersBundle\Controller;

abstract class BaseController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    private $shop = null;

    protected function getConfig()
    {
        return $this->container->getParameter('fgms_special_offers.config');
    }

    protected function getApiKey()
    {
        return $this->getConfig()['api_key'];
    }

    protected function getSecret()
    {
        return $this->getConfig()['secret'];
    }

    protected function getExpired()
    {
        return $this->getConfig()['expired'];
    }

    protected function getStoreRepository()
    {
        $doctrine = $this->getDoctrine();
        return $doctrine->getRepository(\Fgms\SpecialOffersBundle\Entity\Store::class);
    }

    protected function getSpecialOfferRepository()
    {
        $doctrine = $this->getDoctrine();
        return $doctrine->getRepository(\Fgms\SpecialOffersBundle\Entity\SpecialOffer::class);
    }

    protected function getEntityManager()
    {
        $doctrine = $this->getDoctrine();
        return $doctrine->getEntityManager();
    }

    protected function getStoreAddress($mixed)
    {
        if (!($mixed instanceof \Symfony\Component\HttpFoundation\Request)) return $mixed;
        $retr = $mixed->query->get('shop');
        if (!is_string($retr)) throw $this->createBadRequestException(
            '"shop" missing or not string'
        );
        return $retr;
    }

    protected function getStoreName($mixed)
    {
        $mixed = $this->getStoreAddress($mixed);
        return preg_replace('/\\.myshopify\\.com$/u','',$mixed);
    }

    protected function getStore($mixed)
    {
        $mixed = $this->getStoreName($mixed);
        $repo = $this->getStoreRepository();
        return $repo->getByName($mixed);
    }

    protected function getShopify($mixed)
    {
        if ($mixed instanceof \Fgms\SpecialOffersBundle\Entity\Store) {
            $name = $mixed->getName();
            $store = $mixed;
        } else {
            $name = $this->getStoreName($mixed);
            $store = $this->getStore($mixed);
        }
        $shopify = new \Fgms\SpecialOffersBundle\Shopify\Client(
            $this->getApiKey(),
            $this->getSecret(),
            $name
        );
        if (!is_null($store)) $shopify->setToken($store->getAccessToken());
        return $shopify;
    }

    protected function getStoreInformation($mixed)
    {
        if (is_null($this->shop)) {
            $shopify = $this->getShopify($mixed);
            $result = $shopify->call('GET','/admin/shop.json');
            $this->shop = $result->getObject('shop');
        }
        return $this->shop;
    }

    protected function getTimezone($mixed)
    {
        $shop = $this->getStoreInformation($mixed);
        $iana = $shop->getString('iana_timezone');
        return new \DateTimeZone($iana);
    }

    protected function getMoneyWithCurrencyFormat($mixed)
    {
        $shop = $this->getStoreInformation($mixed);
        $fmt = $shop->getString('money_with_currency_format');
        return $fmt;
    }

    protected function createBadRequestException($message = 'Bad Request', $previous = null)
    {
        return new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException($message,$previous);
    }
}
