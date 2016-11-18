<?php

namespace Fgms\SpecialOffersBundle\Controller;

class DefaultController extends BaseController
{
    private function getCurrentStore(\Symfony\Component\HttpFoundation\Request $request)
    {
        $session = $request->getSession();
        $addr = $request->query->get('shop');
        if (!is_null($addr)) {
            $shopify = $this->getShopify($addr);
            if (!$shopify->verify($request)) throw $this->createBadRequestException(
                'Request does not verify'
            );
            $store = $this->getStoreName($addr);
        } else {
            $store = $session->get('store');
            if (is_null($store)) throw $this->createBadRequestException(
                'No store in request or session'
            );
        }
        $entity = $this->getStore($store);
        if (is_null($entity)) throw $this->createBadRequestException(
            'No store "%s"',
            $store
        );
        $session->set('store',$entity->getName());
        return $entity;
    }

    private function getContext(\Fgms\SpecialOffersBundle\Entity\Store $store, array $curr = [])
    {
        return array_merge([
            'store' => $store,
            'api_key' => $this->getApiKey()
        ],$curr);
    }

    public function indexAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $store = $this->getCurrentStore($request);
        $repo = $this->getSpecialOfferRepository();
        $pending = $repo->getByStatus('pending',$store);
        $active = $repo->getByStatus('active',$store);
        $ctx = $this->getContext($store,[
            'pending' => $pending,
            'active' => $active,
            'timezone' => $this->getTimezone($store)
        ]);
        return $this->render('FgmsSpecialOffersBundle:Default:index.html.twig',$ctx);
    }

    public function createAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $store = $this->getCurrentStore($request);
        $ctx = $this->getContext($store);
        return $this->render('FgmsSpecialOffersBundle:Default:create.html.twig',$ctx);
    }

    public function editAction(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        $store = $this->getCurrentStore($request);
        $id = intval($id);
        $repo = $this->getSpecialOfferRepository();
        $offer = $repo->getById($id,$store);
        if (is_null($offer)) throw $this->createNotFoundException(
            sprintf(
                'No SpecialOffer with ID %d in Store %d',
                $id,
                $store->getId()
            )
        );
        $status = $offer->getStatus();
        if ($status !== 'pending') throw $this->createNotFoundException(
            sprintf(
                'SpecialOffer %d has non-pending status "%s"',
                $id,
                $status
            )
        );
        $ctx = $this->getContext($store,[
            'offer' => $offer
        ]);
        return $this->render('FgmsSpecialOffersBundle:Default:edit.html.twig',$ctx);
    }
}
