<?php

namespace Fgms\SpecialOffersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FgmsSpecialOffersBundle:Default:index.html.twig');
    }
  
    public function oauthAction()  {          
      $this->service = $this->container->get('fg.shopify'); 
      $this->shopify = $this->service->get_shopify('FgmsSpecialOffersBundle');
       // this means that good to go
       if (!empty($this->shopify)){ 
         
         $settings = $this->service->get_settings();
         $settings['page']['title'] = 'Special Offers';
         $settings['page']['data'] = 'This is Shopify Special offers';

         return $this->render('FgmsSpecialOffersBundle:Default:index.html.twig', $settings);
       }
       else {
         return $this->service->add_new_store();
       }
    }  
}
