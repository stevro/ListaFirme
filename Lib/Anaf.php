<?php

/*
 *  Developed by Stefan Matei - stev.matei@gmail.com
 */

namespace Stev\ListaFirmeBundle\Lib;

/**
 * Description of Anaf
 *
 * @author stefan
 *
 * https://static.anaf.ro/static/10/Anaf/Informatii_R/documentatie_SW_26092017.txt
 */
class Anaf extends AbstractCIFChecker implements CIFCheckerInterface {

    protected $baseUri = 'https://webservicesp.anaf.ro/PlatitorTvaRest/api/v2/ws/tva';

    /**
     *
     * @var \Guzzle\Http\Client
     */
    protected $client;

    /**
     *
     * @param bool $offline Set it to true if list firme is down or if you want to disable the check. It will make the check to return a mocked(dummy) response.
     * @param bool $enabled
     */
    public function __construct($offline = false, $enabled = true) {
        parent::__construct($offline, $enabled);

        $this->init();
    }

    private function init() {
        $this->client = new \GuzzleHttp\Client();
    }

    public function getCheckerName() {
        return CIFChecker::CHECKER_ANAF;
    }

    /**
     *
     * @param string $cui
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     *
     * 
     */
    protected function check($cui) {

        try {
            $date = new \DateTime();
            $response = $this->client->post($this->baseUri, array(), array('cui' => $cui, 'data' => $date->format('Y-m-d')));

            return $this->parseResponse($response);
        } catch (\GuzzleHttp\Exception\ClientException $e) {

            return $e->getMessage();
        } catch (\Exception $e) {
            return $e->getMessage();
        }


//        switch ($response->getStatusCode()) {
//            case 200:
//                //CIF-ul a fost găsit în baza de date, se returnează datele firmei 
//                return $this->parseResponse($response);
//                break;
//            case 202:
//                //CIF-ul nu a fost găsit în baza de date și este valid
//                return 'CIF-ul nu a fost gasit in baza de date dar este valid';
//                break;
//            default:
//                //CIF-ul nu a fost găsit în baza de date și nu este valid
//                return 'Raspuns necunoscut!';
//                break;
//        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * 
     * Raspunsul va avea urmatoarea structura: 
      {
      "cod":200,
      "message":"SUCCESS",
      "found":[
      {
      "cui": ---codul fiscal---,
      "data": " data_pt_care_se_efectueaza_cautarea",
      "denumire": "-denumire-",
      "adresa": "---adresa---",
      "scpTVA": true -pentru platitor in scopuri de tva / false in cazul in care nu e platitor  in scopuri de TVA
      "data_inceput_ScpTVA": " ",
      "data_sfarsit_ScpTVA": " ",
      "data_anul_imp_ScpTVA": " ",
      "mesaj_ScpTVA": "---MESAJ:(ne)platitor de TVA la data cautata---",
      "dataInceputTvaInc": " ",
      "dataSfarsitTvaInc": " ",
      "dataActualizareTvaInc": " ",
      "dataPublicareTvaInc": " ",
      "tipActTvaInc": " ",
      "statusTvaIncasare":  true -pentru platitor TVA la incasare/ false in cazul in care nu e platitor de TVA la incasare
      "dataInactivare": " ",
      "dataReactivare": " ",
      "dataPublicare": " ",
      "dataRadiere": " ",
      "statusInactivi": true -pentru inactiv / false in cazul in care nu este inactiv
      }

      ]
      }
     */
    protected function parseResponse(\Psr\Http\Message\ResponseInterface $response) {
        $ret = (string) $response->getBody();

        if (stripos($ret, 'Mentenanta')) {
            throw new \Exception('Sistemul ANAF este in mentenanta! Va rugam completati datele manual.');
        }

        $data = json_decode($ret);

        if (false === $data || null === $data) {

            throw new \Exception('CUI-ul nu a putut fi validat! Va rugam completati datele manual.');
        }

        if ($data->cod !== 200) {
            throw new \Exception('CUI-ul nu a putut fi validat! Va rugam completati datele manual.');
        }

        if ($data->message != 'SUCCESS') {
            throw new \Exception('CUI-ul nu a putut fi gasit! Va rugam completati datele manual.');
        }

        return $this->buildResponse($data->found);
    }

    /**
     * 
     * @param array $data
     * @return \Stev\ListaFirmeBundle\Lib\Response
     */
    protected function buildResponse($data) {
        $response = new Response();

        $response->setNume($data->denumire);
        $response->setCui($data->cui);
//        $response->setNrInmatr($data->numar_reg_com);
//        $response->setJudet($data->judet);
//        if (isset($data->localitate)) {
//            $response->setLocalitate($data->localitate);
//        } elseif (isset($data->oras)) {
//            $response->setLocalitate($data->oras);
//        } else {
//            $adresaArr = explode(',', $data->adresa);
//            $response->setLocalitate(array_pop($adresaArr));
//        }

        $response->setAdresa($data->adresa);
        $response->setActualizat($data->data_inceput_ScpTVA);
        $response->setTva((bool) $data->scpTVA);

        return $response;
    }

}
