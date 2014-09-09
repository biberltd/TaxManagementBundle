<?php

namespace BiberLtd\Bundle\TaxManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BiberLtdTaxManagementBundle:Default:index.html.twig', array('name' => $name));
    }
}
