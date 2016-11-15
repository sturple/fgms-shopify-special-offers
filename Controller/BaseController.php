<?php

namespace Fgms\SpecialOffersBundle\Controller;

abstract class BaseController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
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

    protected function getStoreRepository()
    {
        $doctrine = $this->getDoctrine();
        return $doctrine->getRepository(\Fgms\SpecialOffersBundle\Entity\Store::class);
    }

    protected function extractStoreName($store_addr)
    {
        return preg_replace('/\\.myshopify\\.com$/u','',$store_addr);
    }

    protected function getStore($store_addr)
    {
        $repo = $this->getStoreRepository();
        $store_name = $this->extractStoreName($store_addr);
        return $repo->getByName($store_name);
    }

    protected function getShopify($store_addr)
    {
        $shopify = new \Fgms\SpecialOffersBundle\Utility\ShopifyClient(
            $this->getApiKey(),
            $this->getSecret(),
            $this->extractStoreName($store_addr)
        );
        $store = $this->getStore($store_addr);
        if (!is_null($store)) $shopify->setToken($store->getAccessToken());
        return $shopify;
    }

    protected function createBadRequestException($message = 'Bad Request', $previous = null)
    {
        return new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException($message,$previous);
    }
}
