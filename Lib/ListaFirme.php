<?php

/*
 *  Developed by Stefan Matei - stev.matei@gmail.com
 */

namespace Stev\ListaFirmeBundle\Lib;

use GuzzleHttp\Client;

/**
 * Description of ListaFirme
 *
 * @author stefan
 *
 * http://www.verificaretva.ro/serviciul_tva_api_web_service.htm
 */
class ListaFirme {

    protected $baseUri = 'http://www.verificaretva.ro/api/apiv2.aspx';
    protected $username = 'demo';
    protected $password = 'demo';
    protected $offline = false;
    protected $enabled = false;

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
    public function __construct($username, $password, $offline = false, $enabled = true) {
        $this->username = $username;
        $this->password = $password;
        $this->offline = $offline;
        $this->enabled = $enabled;
    }

    /**
     *
     * @param string $cui
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     *
     * {"Raspuns":"valid","Nume":"BORG DESIGN SRL","CUI":"14837428","NrInmatr":"J40/8118/2002",
      "Judet":"BUCURESTI","Localitate":"BUCURESTI","Tip":"STR.","Adresa":"DEMOCRATIEI","Nr":"4",
      "Stare":"INREGISTRAT DIN DATA 25 AUGUST 2006","Actualizat":"2015/01/14","TVA":"1",
      "TVAincasare":"0","DataTVA":"2015/01/14"}
     */
    public function checkCompanyByCUI($cui, \DateTime $date = null) {

        if (false === $this->enabled) {
            return null;
        }

        if (true === $this->offline) {
            return $this->mockResponse($cui);
        }

        $client = new Client();

        $prefix = 'RO';
        if (0 === strpos($cui, $prefix)) {
            $cui = substr($cui, strlen($prefix));
        }

        $date = $date ? $date : new \DateTime();

        $options['body'] = array(
            'nume' => $this->username,
            'pwd' => $this->password,
            'cui' => trim($cui),
            'date' => $date->format('Y/m/d')
        );

        $response = $client->post($this->baseUri, $options);

        $ret = (string) $response->getBody();

        $data = json_decode($ret);

        if (false === $data || null === $data) {
            $ret = trim(str_replace(array(' ', '-'), '_', $ret));

            throw new \Exception(constant("Stev\\ListaFirmeBundle\\Lib\\ListaFirme::$ret"));
        }

        return $data;
    }

    public function mockResponse($cui) {
        $faker = \Faker\Factory::create();
        $faker->addProvider(new \CompanyNameGenerator\FakerProvider($faker));

        return (object) array(
                    'Nume' => $faker->company . ' ' . 'SRL',
                    'CUI' => $cui,
                    'NrInmatr' => 'J40/' . $faker->randomNumber(4) . '/2014',
                    'Judet' => $faker->citySuffix,
                    'Localitate' => $faker->city,
                    'Tip' => 'Str',
                    'Adresa' => $faker->address,
                    'Nr' => $faker->randomNumber(2),
                    'Stare' => 'Inregistrat de curand',
                    'Actualizat' => $faker->date(),
                    'TVA' => rand(0, 1),
                    'TVAincasare' => rand(0, 1),
                    'DataTVA' => $faker->date()
        );
    }

}
