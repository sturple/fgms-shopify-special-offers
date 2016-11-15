<?php

namespace Fgms\SpecialOffersBundle\Controller;

class InstallController extends BaseController
{
    private function verify(\Symfony\Component\HttpFoundation\Request $request)
    {
        $shopify = $this->getShopify($this->getStoreName($request));
        if (!$shopify->verify($request)) throw $this->createBadRequestException('Verification failed');
    }

    private function doneAction()
    {
        //  TODO
        var_dump('Done');
        die();
    }

    public function installAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        //  Already installed
        if ($this->getStore($request)) return $this->doneAction();
        $router = $this->container->get('router');
        $return_url = $router->generate('fgms_special_offers_auth',[],\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        $install_url = sprintf(
            'https://%s/admin/oauth/authorize?client_id=%s&scope=%s&redirect_uri=%s',
            $this->getStoreAddress($request),
            rawurlencode($this->getApiKey()),
            rawurlencode('read_products,write_products'),
            rawurlencode($return_url)
        );
        return $this->redirect($install_url);
    }

    public function authAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->verify($request);
        //  Already installed
        if ($this->getStore($request)) return $this->doneAction();
        $code = $request->query->get('code');
        if (!is_string($code)) throw $this->createBadRequestException('No query string key "code"');
        $shopify = $this->getShopify($this->getStoreName($request));
        $token = $shopify->getToken($code);
        $store = new \Fgms\SpecialOffersBundle\Entity\Store();
        $store->setName($this->getStoreName($request));
        $store->setAccessToken($token->getString('access_token'));
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $em->persist($store);
        $em->flush();
        return $this->doneAction();
    }
}
