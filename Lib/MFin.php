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
class MFin extends AbstractCIFChecker implements CIFCheckerInterface {

    protected $baseUri = 'http://mfinante.ro/infocodfiscal.html';
    protected $pathToPhantom = 'bin/phantomjs';

    public function __construct($offline, $enabled, $pathToPhantom) {
        parent::__construct($offline, $enabled);

        $this->pathToPhantom = $pathToPhantom;
    }

    public function getCheckerName() {
        return CIFChecker::CHECKER_MFIN;
    }
    
    protected function check($cui, $prefix = null) {

        $client = PhantomClient::getInstance();
        $client->getEngine()->setPath($this->pathToPhantom);
        /**
         * @see JonnyW\PhantomJs\Message\Request 
         * */
        $request = $client->getMessageFactory()->createCaptureRequest($this->baseUri, 'POST');
        $request->setDelay(5);
        $request->setRequestData(array('cod' => $cui));
        /**
         * @see JonnyW\PhantomJs\Message\Response 
         * */
        $response = $client->getMessageFactory()->createResponse();

        // Send the request
        $client->send($request, $response);

        if ($response->getStatus() === 200) {

            $company = $this->buildResponse($response->getContent());
            $company->setCui($cui);

            return $company;
        }

        throw new \Exception('CUI-ul transmis nu a putut fi validat! Raspunsul de la server este ' . $response->getStatus());
    }

    /**
     * 
     * @param string $data HTML response from mFin
     */
    protected function parse($data) {
        $crawler = new \Symfony\Component\DomCrawler\Crawler($data);
        $title = $crawler->filter('title');
        if (strtoupper($title->text()) == 'REQUEST REJECTED') {
            throw new \Exception('Site-ul Ministerului de Finante nu este disponibil! Va rugam reincercati in cateva minute.');
        }
        
        if (strstr($data, 'Nu exista agent economic cu acest cod fiscal')) {
            throw new \Exception('CIF-ul introdus nu exista in baza de date Ministerului Finantelor.');
        }
        
        $table = $crawler->filter('#main table:first-child tr');

        if (!count($table)) {
            throw new \Exception('Site-ul Ministerului de Finante este in mentenanta! Va rugam reincercati in cateva minute.');
        }

        $arr = $table->each(function(\Symfony\Component\DomCrawler\Crawler $node, $i) {
            $text = $node->filter('td')->each(function($node, $i) {
                return trim($node->text());
            });

            return $text;
        });

        return $arr;
    }

    protected function buildResponse($data) {

        $data = $this->parse($data);

        $response = new Response();

        foreach ($data as &$tmpData) {
            $tmpData[0] = preg_replace('/\s+/', '', $tmpData[0]);
            $tmpData[0] = strtolower(str_replace(array(':', '*', '(', ')'), '', $tmpData[0]));
            isset($tmpData[1]) ? $tmpData[1] = preg_replace('/[\t]+/', '', preg_replace('/[\n]+/', '', $tmpData[1])) : '';
        }

        foreach ($data as $mFinData) {
            switch ($mFinData[0]) {
                case 'denumireplatitor':
                    $response->setNume($mFinData[1]);
                    break;
                case 'adresa':
                    $response->setAdresa($mFinData[1]);
                    break;
                case 'judetul':
                    $response->setJudet($mFinData[1]);
                    break;
                case 'numardeinmatricularelaregistrulcomertului':
                    $response->setNrInmatr($mFinData[1]);
                    break;
                case 'actautorizare':
                    break;
                case 'codulpostal':
                    break;
                case 'telefon':
                    break;
                case 'staresocietate':
                    $response->setStare($mFinData[1]);
                    break;
                case 'observatiiprivindsocietateacomerciala':
                    break;
                case 'taxapevaloareaadaugatadataluariiinevidenta':
                    if ($mFinData[1] === 'NU') {
                        $response->setTva(false);
                    } else {
                        $response->setTva(true);
                        $response->setDataTva($mFinData[1]);
                    }
                    break;
                case 'staresocietate':
                    $response->setActualizat($mFinData[1]);
                default:
                    break;
            }
        }

        return $response;
    }

}
