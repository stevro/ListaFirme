<?php

namespace Stev\ListaFirmeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{

    /**
     * @Route("/listafirme/{cui}")
     * @Template()
     */
    public function indexAction($cui)
    {
        /* @var $listaFirme \Stev\ListaFirmeBundle\Lib\ListaFirme */
        $listaFirme = $this->get('stev.lista_firme');

        $response = $listaFirme->checkCompanyByCUI($cui);

        var_dump($response);
        die;

        return $response;
    }

}