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

    private function getForm($mixed)
    {
        if ($mixed instanceof \Fgms\SpecialOffersBundle\Entity\SpecialOffer) {
            $offer = $mixed;
            $store = $offer->getStore();
        } else {
            $offer = null;
            $store = $mixed;
        }
        $dt_options = [
            'view_timezone' => $this->getTimezone($store)->getName(),
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy h:mm a'
        ];
        $products = $this->getAllProducts($store,['id','title','variants']);
        $fb = $this->createFormBuilder()
            ->add('title',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => true])
            ->add('subtitle',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => false])
            ->add('start',\Fgms\SpecialOffersBundle\Form\Type\LinkedDateTimeType::class,array_merge($dt_options,[
                'linked' => 'end',
                'first' => true
            ]))
            ->add('end',\Fgms\SpecialOffersBundle\Form\Type\LinkedDateTimeType::class,array_merge($dt_options,[
                'linked' => 'start',
                'first' => false
            ]))
            ->add('summary',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => false,'empty_data' => null])
            ->add('tags',\Fgms\SpecialOffersBundle\Form\Type\TagsType::class,['required' => false])
            ->add('variantIds',\Fgms\SpecialOffersBundle\Form\Type\VariantsType::class,['products' => $products,'label' => 'Variants'])
            ->add('discount',\Fgms\SpecialOffersBundle\Form\Type\DiscountType::class,['money_with_currency_format' => $this->getMoneyWithCurrencyFormat($store)])
            ->add('submit',\Symfony\Component\Form\Extension\Core\Type\SubmitType::class);
        $retr = $fb->getForm();
        if (!is_null($offer)) $retr->setData([
            'title' => $offer->getTitle(),
            'start' => $offer->getStart(),
            'end' => $offer->getEnd(),
            'summary' => $offer->getSummary(),
            'tags' => $offer->getTags(),
            'variantIds' => $offer->getVariantIds(),
            'discount' => [
                'percent' => $offer->getDiscountPercent(),
                'cents' => $offer->getDiscountCents()
            ]
        ]);
        return $retr;
    }

    private function toCents($str)
    {
        if (is_null($str)) return null;
        try {
            return \Fgms\SpecialOffersBundle\Utility\Convert::toCents($str);
        } catch (\Fgms\SpecialOffersBundle\Exception\ConvertException $e) {
            throw $this->createBadRequestException('Unrecognized dollars format',$e);
        }
    }

    private function toPercent($str)
    {
        if (is_null($str)) return null;
        try {
            return \Fgms\SpecialOffersBundle\Utility\Convert::toInteger($str);
        } catch (\Fgms\SpecialOffersBundle\Exception\ConvertException $e) {
            throw $this->createBadRequestException('Unrecognized percent format',$e);
        }
    }

    private function fromForm(\Symfony\Component\Form\FormInterface $form, $mixed)
    {
        if ($mixed instanceof \Fgms\SpecialOffersBundle\Entity\Store) {
            $offer = new \Fgms\SpecialOffersBundle\Entity\SpecialOffer();
            $offer->setStore($mixed);
        } else {
            $offer = $mixed;
        }
        $data = $form->getData();
        $offer->setTitle($data['title'])
            ->setSubtitle($data['subtitle'])
            ->setSummary($data['summary']);
        $start = $data['start'];
        $end = $data['end'];
        if ($start->getTimestamp() > $end->getTimestamp()) throw $this->createBadRequestException('SpecialOffer ends before it begins');
        $offer->setStart($start)
            ->setEnd($end);
        $discount = $data['discount'];
        $offer->setDiscountCents($discount['cents'])
            ->setDiscountPercent($discount['percent']);
        $vids = $data['variantIds'];
        $offer->setVariantIds($vids);
        $tags = $data['tags'];
        $offer->setTags($tags);
        return $offer;
    }

    private function getAllProducts(\Fgms\SpecialOffersBundle\Entity\Store $store, array $fields = null)
    {
        $shopify = $this->getShopify($store);
        $count = $shopify->call('GET','/admin/products/count')->getInteger('count');
        $products = [];
        $page = 1;
        //  This is the maximum according to the Shopify API documentation:
        //
        //  https://help.shopify.com/api/reference/product#index
        $args = ['limit' => 250];
        if (!is_null($fields)) $args['fields'] = implode(',',$fields);
        do {
            $args['page'] = $page;
            $curr = $shopify->call('GET','/admin/products',$args)->getArray('products');
            foreach ($curr as $product) $products[] = $product;
            ++$page;
        } while (count($products) !== $count);
        return $products;
    }

    private function getFormContext(\Symfony\Component\Form\FormInterface $form, \Fgms\SpecialOffersBundle\Entity\Store $store, array $args = [])
    {
        $ctx = $this->getContext($store,[
            'form' => $form->createView()
        ]);
        return array_merge($ctx,$args);
    }

    public function indexAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $store = $this->getCurrentStore($request);
        $repo = $this->getSpecialOfferRepository();
        $pending = $repo->getByStatus('pending',$store);
        $active = $repo->getByStatus('active',$store);
        $max = $this->getExpired();
        $expired = $repo->getByStatus('expired',$store,$max);
        $ctx = $this->getContext($store,[
            'pending' => $pending,
            'active' => $active,
            'expired' => $expired,
            'max_expired' => $max,
            'timezone' => $this->getTimezone($store),
            'money_with_currency_format' => $this->getMoneyWithCurrencyFormat($store)
        ]);
        return $this->render('FgmsSpecialOffersBundle:Default:index.html.twig',$ctx);
    }

    private function create(\Symfony\Component\Form\FormInterface $form, \Fgms\SpecialOffersBundle\Entity\Store $store)
    {
        $offer = $this->fromForm($form,$store);
        $em = $this->getEntityManager();
        $em->persist($offer);
        $em->flush();
        return $this->redirectToRoute('fgms_special_offers_edit',['id' => $offer->getId()]);
    }

    public function createAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $store = $this->getCurrentStore($request);
        $form = $this->getForm($store);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) return $this->create($form,$store);
        $ctx = $this->getFormContext($form,$store);
        return $this->render('FgmsSpecialOffersBundle:Default:create.html.twig',$ctx);
    }

    private function getSpecialOfferById(\Fgms\SpecialOffersBundle\Entity\Store $store, $id)
    {
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
        return $offer;
    }

    public function cloneAction(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        $store = $this->getCurrentStore($request);
        $offer = $this->getSpecialOfferById($store,$id);
        $form = $this->getForm($offer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) return $this->create($form,$store);
        $ctx = $this->getFormContext($form,$store,[
            'offer' => $offer
        ]);
        return $this->render('FgmsSpecialOffersBundle:Default:clone.html.twig',$ctx);
    }

    private function assertStatus(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer, $expected)
    {
        $status = $offer->getStatus();
        if ($status !== $expected) throw $this->createNotFoundException(
            sprintf(
                'SpecialOffer %d has non-%s status "%s"',
                $offer->getId(),
                $expected,
                $status
            )
        );
    }

    public function editAction(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        $store = $this->getCurrentStore($request);
        $offer = $this->getSpecialOfferById($store,$id);
        $this->assertStatus($offer,'pending');
        $form = $this->getForm($offer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $offer = $this->fromForm($form,$offer);
            $em = $this->getEntityManager();
            $em->persist($offer);
            $em->flush();
        }
        $ctx = $this->getFormContext($form,$store,[
            'offer' => $offer
        ]);
        return $this->render('FgmsSpecialOffersBundle:Default:edit.html.twig',$ctx);
    }

    private function cancelPending(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        $now = new \DateTime();
        $offer->setReverted($now)
            ->setApplied($now)
            ->setStatus('expired');
        $em = $this->getEntityManager();
        $em->persist($offer);
        $em->flush($offer);
    }

    private function cancelActive(\Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer)
    {
        $strat = new \Fgms\SpecialOffersBundle\Strategy\SpecialOfferStrategy($this->getShopify($offer->getStore()));
        $em = $this->getEntityManager();
        $changes = $strat->revert($offer);
        foreach ($changes as $change) {
            $offer->addPriceChange($change);
            $em->persist($change);
        }
        $offer->setStatus('expired')
            ->setReverted(new \DateTime());
        $em->persist($offer);
        $em->flush();
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch(
            'specialoffers.started',
            new \Fgms\SpecialOffersBundle\Event\PriceChangeEvent($offer,$changes)
        );
    }

    public function cancelAction(\Symfony\Component\HttpFoundation\Request $request, $id)
    {
        $store = $this->getCurrentStore($request);
        $offer = $this->getSpecialOfferById($store,$id);
        $status = $offer->getStatus();
        if ($status === 'pending') $this->cancelPending($offer);
        else if ($status === 'active') $this->cancelActive($offer);
        else throw $this->createNotFoundException(
            sprintf(
                'SpecialOffer %d is expired',
                $offer->getId()
            )
        );
        return $this->redirectToRoute('fgms_special_offers_homepage');
    }
}
