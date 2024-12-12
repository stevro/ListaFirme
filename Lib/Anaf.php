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
 * https://webservicesp.anaf.ro/PlatitorTvaRest/api/v3/
 */
class Anaf extends AbstractCIFChecker implements CIFCheckerInterface {

    protected $baseUri = 'https://webservicesp.anaf.ro/PlatitorTvaRest/api/v8/ws/tva';

    /**
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     *
     * @param bool $offline Set it to true if the api is down or if you want to disable the check. It will make the check to return a mocked(dummy) response.
     * @param bool $enabled
     */
    public function __construct($offline = false, $enabled = true) {
        parent::__construct($offline, $enabled);
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
     * Momentan functioneaza doar pentru verificarea unui singur CUI.
     * API-ul de la anaf permite verificare mai multor CUI-uri simultan.
     */
    protected function check($cui, $prefix = null) {
        $this->init();
        try {
            $date = new \DateTime();

            $body = array(array('cui' => $cui, 'data' => $date->format('Y-m-d')));

            //http://docs.guzzlephp.org/en/latest/request-options.html#json
            $response = $this->client->request('POST', $this->baseUri, array(
//                'headers' => array(
//                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36'
//                ),
                \GuzzleHttp\RequestOptions::JSON => $body,
            ));

            return $this->parseResponse($response);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $e->getMessage();
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            return $e->getMessage();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * Raspunsul va avea urmatoarea structura:
    * {
    * "cod":200,
    * "message":"SUCCESS",
    * "found":[
     * {
     * "date_generale": {
     * "cui": 34150371,
     * "data": "2024-12-12",
     * "denumire": "NIMA SOFTWARE SRL",
     * "adresa": "JUD. IAŞI, MUN. IAŞI, STR. ELENA DOAMNA, NR.20-22, BIROUL 28, ET.3",
     * "nrRegCom": "J22/314/2015",
     * "telefon": "0743330190",
     * "fax": "",
     * "codPostal": "700398",
     * "act": "",
     * "stare_inregistrare": "INREGISTRAT din data 25.02.2015",
     * "data_inregistrare": "2015-02-25",
     * "cod_CAEN": "6311",
     * "iban": "",
     * "statusRO_e_Factura": true,
     * "data_inreg_Reg_RO_e_Factura": "01.01.2024",
     * "organFiscalCompetent": "Administraţia Judeţeană a Finanţelor Publice Iaşi",
     * "forma_de_proprietate": "PROPR.PRIVATA-CAPITAL PRIVAT AUTOHTON",
     * "forma_organizare": "PERSOANA JURIDICA",
     * "forma_juridica": "SOCIETATE COMERCIALĂ CU RĂSPUNDERE LIMITATĂ"
     * },
     * "inregistrare_scop_Tva": {
     * "scpTVA": true,
     * "perioade_TVA": [
     * {
     * "data_inceput_ScpTVA": "2021-01-13",
     * "data_sfarsit_ScpTVA": "",
     * "data_anul_imp_ScpTVA": "",
     * "mesaj_ScpTVA": ""
     * },
     * {
     * "data_inceput_ScpTVA": "2017-10-01",
     * "data_sfarsit_ScpTVA": "2020-12-02",
     * "data_anul_imp_ScpTVA": "2020-12-29",
     * "mesaj_ScpTVA": "Anularea înregistrarii în scopuri de TVA a fost efectuata din oficiu, potrivit dispozitiilor Art.316 alin.(11) lit.a) din Legea nr.227/2015 privind Codul fiscal, cu modificarile si completarile ulterioare"
     * }
     * ]
     * },
     * "inregistrare_RTVAI": {
     * "dataInceputTvaInc": "",
     * "dataSfarsitTvaInc": "",
     * "dataActualizareTvaInc": "",
     * "dataPublicareTvaInc": "",
     * "tipActTvaInc": "",
     * "statusTvaIncasare": false
     * },
     * "stare_inactiv": {
     * "dataInactivare": "2020-12-02",
     * "dataReactivare": "2020-12-18",
     * "dataPublicare": "2020-12-11",
     * "dataRadiere": "",
     * "statusInactivi": false
     * },
     * "inregistrare_SplitTVA": {
     * "dataInceputSplitTVA": "",
     * "dataAnulareSplitTVA": "",
     * "statusSplitTVA": false
     * },
     * "adresa_sediu_social": {
     * "sdenumire_Strada": "Str. Elena Doamna",
     * "snumar_Strada": "20-22",
     * "sdenumire_Localitate": "Mun. Iaşi",
     * "scod_Localitate": "230",
     * "sdenumire_Judet": "IAŞI",
     * "scod_Judet": "22",
     * "scod_JudetAuto": "IS",
     * "stara": "",
     * "sdetalii_Adresa": "BIROUL 28",
     * "scod_Postal": "700398"
     * },
     * "adresa_domiciliu_fiscal": {
     * "ddenumire_Strada": "Str. Elena Doamna",
     * "dnumar_Strada": "20-22",
     * "ddenumire_Localitate": "Mun. Iaşi",
     * "dcod_Localitate": "230",
     * "ddenumire_Judet": "IAŞI",
     * "dcod_Judet": "22",
     * "dcod_JudetAuto": "IS",
     * "dtara": "",
     * "ddetalii_Adresa": "BIROUL 28",
     * "dcod_Postal": "700398"
     * }
     * },
     *
* ]
    * }
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

        return $this->buildResponse($data->found[0]);
    }

    /**
     *
     * @param array $data
     * @return \Stev\ListaFirmeBundle\Lib\Response
     */
    protected function buildResponse($data) {
        $response = new Response();

        $info = $data->date_generale;

        $response->setNume($info->denumire);
        $response->setCui($info->cui);
        $response->setNrInmatr($info->nrRegCom);
        $response->setStare($info->stare_inregistrare);

        $domiciliuFiscal = $data->adresa_domiciliu_fiscal;

        $response->setAdresa(implode(',',[$domiciliuFiscal->ddenumire_Judet, $domiciliuFiscal->ddenumire_Localitate, $domiciliuFiscal->ddenumire_Strada, $domiciliuFiscal->dnumar_Strada, $domiciliuFiscal->ddetalii_Adresa]));
        $response->setJudet($domiciliuFiscal->ddenumire_Judet);
        $response->setLocalitate($domiciliuFiscal->ddenumire_Localitate);
        $response->setNr($domiciliuFiscal->dnumar_Strada);

        $response->setActualizat($data->inregistrare_scop_Tva->perioade_TVA[0]->data_inceput_ScpTVA);
        $response->setDataTva($data->inregistrare_scop_Tva->perioade_TVA[0]->data_inceput_ScpTVA);
        $response->setTva((bool) $data->inregistrare_scop_Tva->scpTVA);

        return $response;
    }

}
