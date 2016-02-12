<?php

/*
 * This code belongs to NIMA Software SRL | nimasoftware.com
 * For details contact contact@nimasoftware.com
 */

namespace Stev\ListaFirmeBundle\Lib;

use JonnyW\PhantomJs\Client as PhantomClient;

/**
 * Description of MFin
 *
 * @author stefan
 */
class MFin extends AbstractCIFChecker implements CIFCheckerInterface
{

    protected $baseUri = 'http://mfinante.ro/infocodfiscal.html';

    public function __construct($offline, $enabled)
    {
        parent::__construct($offline, $enabled);
    }

    protected function check($cui)
    {
//        $options['form_params'] = array(
//            'cod' => trim($cui),
//        );
//
//        $response = $this->client->post($this->baseUri, $options);
//
//        $ret = (string) $response->getBody();

        $pathToPhatomJs = '/home/vagrant/phantomjs-2.1.1-linux-x86_64/bin/phantomjs';

        $pathToJsScript = '/home/vagrant/cp.dev/web/bundles/stevlistafirme/browser.js';

        $stdOut = exec(sprintf('%s %s 2>&1', $pathToPhatomJs, $pathToJsScript), $out);

        var_dump($stdOut, $out);
        die;



//        $data = $this->parse($ret);
//
//        if (false === $data || null === $data) {
//            throw new \Exception('Invalid response from mFin');
//        }

        return $this->buildResponse($data);
    }

    /**
     * 
     * @param string $data HTML response from mFin
     */
    protected function parse($data)
    {
        $crawler = new \Symfony\Component\DomCrawler\Crawler($data);
        var_dump($crawler->html());
        die;
        $tables = $crawler->filter('body > table');
        var_dump($tables);
        die;
        foreach ($tables as $table) {
            dump($table->html());
        }
    }

    protected function buildResponse($data)
    {
        $response = new Response();

//        $response->setNume($data->);
//        $response->setCui($data->);
//        $response->setNrInamtr($data->);
//        $response->setJudet($data->);
//        $response->setLocalitate($data->);
//        $response->setTip($data->);
//        $response->setAdresa($data->);
//        $response->setNr($data->);
//        $response->setStare($data->);
//        $response->setActualizat($data->);
//        $response->setTva($data->);
//        $response->setTvaIncasare($data->);
//        $response->setDataTva($data->);

        return $response;
    }

}
