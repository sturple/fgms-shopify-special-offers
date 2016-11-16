<?php

namespace Fgms\SpecialOffersBundle\Controller;

class InstallController extends BaseController
{
    private function verify(\Symfony\Component\HttpFoundation\Request $request)
    {
        $shopify = $this->getShopify($this->getStoreName($request));
        if (!$shopify->verify($request)) throw $this->createBadRequestException('Verification failed');
    }

    private function done(\Symfony\Component\HttpFoundation\Request $request, \Fgms\SpecialOffersBundle\Entity\Store $store)
    {
        $session = $request->getSession();
        $session->set('store',$store->getName());
        $router = $this->container->get('router');
        return $this->redirect(
            $router->generate('fgms_special_offers_homepage',[],\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }

    private function check(\Symfony\Component\HttpFoundation\Request $request)
    {
        if ($this->getStore($request)) throw $this->createBadRequestException('Already installed');
    }

    public function installAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->check($request);
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
        $this->check($request);
        $this->verify($request);
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
        return $this->done($request,$store);
    }
}
