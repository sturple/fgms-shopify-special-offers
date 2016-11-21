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

    private function getForm(\DateTimeZone $tz, \Fgms\SpecialOffersBundle\Entity\SpecialOffer $offer = null)
    {
        $fb = $this->createFormBuilder()
            ->add('title',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => true])
            ->add('subtitle',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => false])
            ->add('start',\Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,['view_timezone' => $tz->getName()])
            ->add('end',\Symfony\Component\Form\Extension\Core\Type\DateTimeType::class,['view_timezone' => $tz->getName()])
            ->add('summary',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => false,'empty_data' => null])
            ->add('tags',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => false])
            ->add('variantIds',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => true])
            ->add('discountDollars',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => false,'empty_data' => null])
            ->add('discountPercent',\Symfony\Component\Form\Extension\Core\Type\TextType::class,['required' => false,'empty_data' => null])
            ->add('submit',\Symfony\Component\Form\Extension\Core\Type\SubmitType::class);
        $retr = $fb->getForm();
        if (!is_null($offer)) $retr->setData([
            'title' => $offer->getTitle(),
            'start' => $offer->getStart(),
            'end' => $offer->getEnd(),
            'summary' => $offer->getSummary(),
            'tags' => implode(', ',$offer->getTags()),
            'variantIds' => implode(', ',$offer->getVariantIds()),
            'discountDollars' => sprintf(
                '%.2f',
                round(floatval($offer->getDiscountCents())/100.0,2)
            ),
            'discountPercent' => (string)$offer->getDiscountPercent()
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
        $pct = $this->toPercent($data['discountPercent']);
        $cents = $this->toCents($data['discountDollars']);
        if (is_null($pct) === is_null($cents)) throw $this->createBadRequestException('Both cents and percentage or neither');
        if (is_null($pct)) {
            $offer->setDiscountCents($cents)
                ->setDiscountPercent(null);
        } else {
            $offer->setDiscountCents(null)
                ->setDiscountPercent($pct);
        }
        $vids = preg_split('/,\\s*/u',$data['variantIds']);
        try {
            $vids = array_map(function ($str) { return \Fgms\SpecialOffersBundle\Utility\Convert::toInteger($str);  },$vids);
        } catch (\Fgms\SpecialOffersBundle\Exception\ConvertException $e) {
            throw $this->createBadRequestException('Unrecognized variant ID format',$e);
        }
        $offer->setVariantIds($vids);
        $tags = $data['tags'];
        $tags = is_null($tags) ? [] : preg_split('/,\\s*/u',$tags);
        $offer->setTags($tags);
        return $offer;
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
            'timezone' => $this->getTimezone($store)
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
        $tz = $this->getTimezone($store);
        $form = $this->getForm($tz);
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
        $tz = $this->getTimezone($store);
        $form = $this->getForm($tz,$offer);
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
        $tz = $this->getTimezone($store);
        $form = $this->getForm($tz,$offer);
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
