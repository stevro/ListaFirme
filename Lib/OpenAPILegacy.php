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
 * http://legacy.openapi.ro
 */
class OpenAPILegacy extends AbstractCIFChecker implements CIFCheckerInterface
{

    protected $baseUri = 'http://legacy.openapi.ro/api/companies/';

    /**
     *
     * @var \Guzzle\Http\Client
     */
    protected $client;

    /**
     *
     * @param bool $offline Set it to true if list firme is down or if you want to disable the check. It will make the check to return a mocked(dummy) response.
     */
    public function __construct($offline = false, $enabled = true)
    {
        parent::__construct($offline, $enabled);
    }

    public function getCheckerName() {
        return 'openApiLegacy';
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
    protected function check($cui, $prefix = null)
    {
        $this->client = new \GuzzleHttp\Client();
        $this->baseUri .= $cui . '.json';
        try {
            $response = $this->client->get($this->baseUri);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return null;
        }
        if ($response->getStatusCode() != 200) {
            //CUI-ul nu a fost gasit in OpenAPI
            return null;
        }
        $ret = (string) $response->getBody();
        $data = json_decode($ret);
        if (false === $data || null === $data) {
            $ret = trim(str_replace(array(' ', '-'), '_', $ret));
            throw new \Exception('CUI-ul nu a putut fi validat!');
        }
        return $this->buildResponse($data);
    }

    /**
     * 
     * @param array $data
     * {"cif":"13548146","address":"B-dul Mihai Viteazu 7",
     * "city":"Sibiu","fax":null,"name":"Cubus Arts S.R.L.",
     * "phone":"0269232192","registration_id":"J32/508/2000",
     * "authorization_number":null,"company_status":"Inregistrat Din Data 23 November 2000",
     * "state":"Sibiu","vat":"1","zip":"550350",
     * "created_at":"2012-01-20T21:48:09.000+00:00","updated_at":"2015-05-09T18:10:09.000+00:00","company_status_updated_at":"2000-11-23"}
     */
    protected function buildResponse($data)
    {
        $response = new Response();
        $response->setNume($data->name);
        $response->setCui($data->cif);
        $response->setNrInmatr($data->registration_id);
        $response->setJudet($data->state);
        $response->setLocalitate($data->city);
        $response->setAdresa($data->address);
        $response->setActualizat($data->company_status);
        $response->setTva((bool) $data->vat);
        return $response;
    }

}
