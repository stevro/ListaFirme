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
 * http://www.verificaretva.ro/serviciul_tva_api_web_service.htm
 */
class ListaFirme extends AbstractCIFChecker implements CIFCheckerInterface
{

    protected $baseUri = 'http://www.verificaretva.ro/api/apiv2.aspx';
    protected $username = 'demo';
    protected $password = 'demo';

    const ERROR_001_INVALID_RESPONSE = 'Eroare aplicatie. Va rugam sa contactati echipa de suport tehnic.';
    const ERROR_002_INVALID_CUI = 'CUI-ul transmis nu este valid conform algoritmului de validare.';
    const ERROR_003_AUTHENTICATION_FAILED = 'Numarul de verificari gratuite zilnice a atins limita maxima / Completati corect numele de utilizator si parola pentru a continua.';
    const ERROR_004_LIMIT_EXCEEDED = 'Licenta trebuie upgradata pentru a permite un numar mai mare de verificari. Va rugam sa contactati echipa de suport tehnic.';
    const ERROR_006_INCORRECT_DATE = 'Data Tranzactiei este incorecta. Incercati AAAA/LL/ZZ';
    const ERROR_007_NON_EXISTENT_CUI = 'CUI-ul nu exista in sursele oficiale.';
    const ERROR_008_SOURCES_UNAVAILABLE = 'Sursele oficiale de date nu sunt disponibile.';

    /**
     *
     * @param string $username
     * @param string $password
     * @param bool $offline Set it to true if list firme is down or if you want to disable the check. It will make the check to return a mocked(dummy) response.
     */
    public function __construct($username, $password, $offline = false, $enabled = true)
    {
        parent::__construct($offline, $enabled);

        $this->username = $username;
        $this->password = $password;
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
        $date = new \DateTime();

        $options['form_params'] = array(
            'nume' => $this->username,
            'pwd' => $this->password,
            'cui' => trim($cui),
            'date' => $date->format('Y/m/d')
        );

        $response = $this->client->post($this->baseUri, $options);

        $ret = (string) $response->getBody();

        $data = json_decode($ret);

        if (false === $data || null === $data) {
            $ret = trim(str_replace(array(' ', '-'), '_', $ret));

            throw new \Exception(constant("Stev\\ListaFirmeBundle\\Lib\\ListaFirme::$ret"));
        }

        return $this->buildResponse($data);
    }

    /**
     * 
     * @param array $data
     * {"Raspuns":"valid","Nume":"BORG DESIGN SRL","CUI":"14837428","NrInmatr":"J40/8118/2002",
      "Judet":"BUCURESTI","Localitate":"BUCURESTI","Tip":"STR.","Adresa":"DEMOCRATIEI","Nr":"4",
      "Stare":"INREGISTRAT DIN DATA 25 AUGUST 2006","Actualizat":"2015/01/14","TVA":"1",
      "TVAincasare":"0","DataTVA":"2015/01/14"}
     */
    protected function buildResponse($data)
    {
        $response = new Response();

        $response->setNume($data->Nume);
        $response->setCui($data->CUI);
        $response->setNrInamtr($data->NrInmatr);
        $response->setJudet($data->Judet);
        $response->setLocalitate($data->Localitate);
        $response->setTip($data->Tip);
        $response->setAdresa($data->Adresa);
        $response->setNr($data->Nr);
        $response->setStare($data->Stare);
        $response->setActualizat($data->Actualizat);
        $response->setTva($data->TVA);
        $response->setTvaIncasare($data->TVAincasare);
        $response->setDataTva($data->DataTVA);

        return $response;
    }

}
