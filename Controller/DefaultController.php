<?php

namespace Fgms\SpecialOffersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FgmsSpecialOffersBundle:Default:index.html.twig');
    }
}
