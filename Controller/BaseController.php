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

    protected function getShopify($store_name)
    {
        return new \Fgms\SpecialOffersBundle\Utility\ShopifyClient(
            $this->getApiKey(),
            $this->getSecret(),
            $store_name
        );
    }

    protected function createBadRequestException($message = 'Bad Request', $previous = null)
    {
        return new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException($message,$previous);
    }
}
