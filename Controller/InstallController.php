<?php

namespace Fgms\SpecialOffersBundle\Controller;

class InstallController extends BaseController
{
    public function installAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $store_name = $request->query->get('shop');
        if (is_null($store_name) || !is_string($store_name)) throw $this->createBadRequestException(
            '"shop" missing or not string'
        );
        $router = $this->container->get('router');
        $return_url = $router->generate('fgms_special_offers_auth',[],\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        $install_url = sprintf(
            'https://%s/admin/oauth/authorize?client_id=%s&scope=%s&redirect_uri=%s',
            $store_name,
            rawurlencode($this->getApiKey()),
            rawurlencode('read_products,write_products'),
            rawurlencode($return_url)
        );
        return $this->redirect($install_url);
    }
}
