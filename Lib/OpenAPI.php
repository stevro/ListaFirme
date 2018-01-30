<?php

/*
 *  Developed by Stefan Matei - stev.matei@gmail.com
 */

namespace Stev\ListaFirmeBundle\Lib;

/**
 * Description of ListaFirme
 *
 * @author stefan
 *
 * http://openapi.ro
 */
class OpenAPI extends AbstractCIFChecker implements CIFCheckerInterface
{

    protected $baseUri = 'https://api.openapi.ro/api/companies/';

    /**
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;
    protected $apiKey;

    /**
     *
     * @param bool $offline Set it to true if list firme is down or if you want to disable the check. It will make the check to return a mocked(dummy) response.
     */
    public function __construct($offline = false, $enabled = true, $apiKey)
    {
        parent::__construct($offline, $enabled);

        $this->apiKey = $apiKey;

        $this->init();
    }

    private function init()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function getCheckerName() {
        return CIFChecker::CHECKER_OPEN_API;
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
    protected function check($cui)
    {
        $this->baseUri .= $cui;
        try {

            $response = $this->client->get($this->baseUri, array('headers' => array('x-api-key' => $this->apiKey)));
        } catch (\GuzzleHttp\Exception\ClientException $e) {

            return $e->getMessage();
        }

        switch ($response->getStatusCode()) {
            case 200:
                //CIF-ul a fost găsit în baza de date, se returnează datele firmei 
                return $this->parseResponse($response);
                break;
            case 202:
                //CIF-ul nu a fost găsit în baza de date și este valid
                return 'CIF-ul nu a fost gasit in baza de date dar este valid';
                break;
            default:
                //CIF-ul nu a fost găsit în baza de date și nu este valid
                return 'Raspuns necunoscut!';
                break;
        }
    }

    protected function parseResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $ret = (string) $response->getBody();

        $data = json_decode($ret);

        if (false === $data || null === $data) {

            throw new \Exception('CUI-ul nu a putut fi validat!');
        }

        return $this->buildResponse($data);
    }

    /**
     * 
     * @param array $data
     * {
      "ultima_prelucrare": "2015-06-22",
      "ultima_declaratie": "2015-06-19",
      "tva_la_incasare": [],
      "tva": null,
      "telefon": null,
      "stare": "INREGISTRAT din data 25 Februarie 2015",
      "radiata": false,
      "numar_reg_com": "J22/314/2015",
      "meta": {
      "updated_at": "2016-09-10T00:41:59.028403",
      "last_changed_at": null
      },
      "judet": "Iași",
      "impozit_profit": null,
      "impozit_micro": "2015-02-27",
      "fax": null,
      "denumire": "Nima Software S.R.L.",
      "cod_postal": null,
      "cif": "34150371",
      "adresa": "Str. Ion Creangă, 52, Iași",
      "act_autorizare": null,
      "accize": null
      }
     */
    protected function buildResponse($data)
    {
        $response = new Response();

        $response->setNume($data->denumire);
        $response->setCui($data->cif);
        $response->setNrInmatr($data->numar_reg_com);
        $response->setJudet($data->judet);

        if (isset($data->localitate)) {
            $response->setLocalitate($data->localitate);
        } elseif (isset($data->oras)) {
            $response->setLocalitate($data->oras);
        } else {
            $adresaArr = explode(',', $data->adresa);
            $response->setLocalitate(array_pop($adresaArr));
        }

        $response->setAdresa($data->adresa);
        $response->setActualizat($data->stare);
        $response->setTva((bool) $data->tva);

        return $response;
    }

}
