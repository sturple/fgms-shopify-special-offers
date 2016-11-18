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

    public function createAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $store = $this->getCurrentStore($request);
        $tz = $this->getTimezone($store);
        $form = $this->getForm($tz);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $offer = $this->fromForm($form,$store);
            $em = $this->getEntityManager();
            $em->persist($offer);
            $em->flush();
            return $this->redirectToRoute('fgms_special_offers_edit',['id' => $offer->getId()]);
        }
        $ctx = $this->getContext($store,[
            'form' => $form->createView()
        ]);
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
        $tz = $this->getTimezone($store);
        $form = $this->getForm($tz,$offer);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $offer = $this->fromForm($form,$offer);
            $em = $this->getEntityManager();
            $em->persist($offer);
            $em->flush();
        }
        $ctx = $this->getContext($store,[
            'offer' => $offer,
            'form' => $form->createView()
        ]);
        return $this->render('FgmsSpecialOffersBundle:Default:edit.html.twig',$ctx);
    }
}
