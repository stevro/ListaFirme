<?php

/*
 * This code belongs to NIMA Software SRL | nimasoftware.com
 * For details contact contact@nimasoftware.com
 */

namespace Stev\ListaFirmeBundle\Lib;

/**
 * Description of Vies
 *
 * @author stefan
 */
class Vies extends AbstractCIFChecker implements CIFCheckerInterface
{

    private $url = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
    private $soapClient;
    private $logger;

    const INVALID_INPUT = 'INVALID_INPUT';
    const GLOBAL_MAX_CONCURRENT_REQ = 'GLOBAL_MAX_CONCURRENT_REQ';
    const MS_MAX_CONCURRENT_REQ = 'MS_MAX_CONCURRENT_REQ';
    const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
    const MS_UNAVAILABLE = 'MS_UNAVAILABLE';
    const TIMEOUT = 'TIMEOUT';

    /**
     * http://ec.europa.eu/taxation_customs/vies/viesspec.do
     * WSDL Doc http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl
     * To unblock your IP send an email to TAXUD-VIESWEB@ec.europa.eu
     */
    public function __construct($offline = false, $enabled = true, \Psr\Log\LoggerInterface $logger)
    {
        parent::__construct($offline, $enabled);

        $this->logger = $logger;
    }

    private function connect()
    {
        if ($this->soapClient instanceof \SoapClient) {
            //reuse the connection
            $this->logger->info('Reusing SOAP connection for VIES');
            return $this->soapClient;
        }
        try {
            $this->logger->info('Connecting to VIES');
            $this->soapClient = new \SoapClient($this->url, array("connection_timeout" => 5));
            $this->logger->info('CONNECTED');
            return $this->soapClient;
        } catch (\SoapFault $e) {
            $this->logger->alert('Unable to connect to VIES API. Reason: ' . $e->getMessage());
            $this->logger->alert('Soap Fault details: ' . (string) $e);
        }

        $this->logger->alert('The connection to VIES API is timeout');
    }

    public function getCheckerName()
    {
        return CIFChecker::CHECKER_VIES;
    }

    /**
     *
     * @param string $vatNumber - It can be in the form of DE231231. You must provide the VAT NUMBER with country code
     * @throws \InvalidArgumentException
     * @return stdClass | null
     *
     * 
     */
    protected function check($vatNumber, $prefix = null)
    {
        $this->connect();

        if (!$this->soapClient) {
            return;
        }

        if (!is_string($vatNumber)) {
            throw new \InvalidArgumentException('vatNumber must be a string. ' . gettype($vatNumber) . ' provided instead');
        }

        $vatNumber = \Stev\ListaFirmeBundle\Util\VatNumber::clean($vatNumber);

        if (\Stev\ListaFirmeBundle\Util\VatNumber::hasPrefix($vatNumber)) {
            $vat = substr($vatNumber, 2);
            $countryCode = substr($vatNumber, 0, 2);
        } else {
            $vat = $vatNumber;
            $countryCode = $prefix;
        }

        if (!$countryCode) {
            throw new \InvalidArgumentException('The VAT Number must start with the country code, e.g. DE999999');
        }

        if (!$vat) {
            throw new \InvalidArgumentException('You must specify a vat number!');
        }

        $countryCode = ($countryCode == 'GR') ? 'EL' : $countryCode;

        try {
            /*
              +"countryCode": "RO"
              +"vatNumber": "36083708"
              +"requestDate": "2017-09-28+02:00"
              +"valid": true
              +"name": "NIMA SOFTWARE SRL"
              +"address": """
              MUN. IAŞI\n
              STR. ION CREANGĂ Nr. 52\n
              Bl.  S1\n
              Sc. B\n
              Et. 1\n
              Ap. 7
              """
             */
            $response = $this->soapClient->checkVat(array(
                'countryCode' => $countryCode,
                'vatNumber' => $vat//'739128'//$this->getCif()
            ));

            if ($response->valid === false) {
                return 'VAT number not valid or unallocated';
            }
        } catch (\Exception $e) {
            $this->logger->error("Checking VIES for vat {$vat} and country {$countryCode} has failed");

            $faultString = strtoupper($e->getMessage());
            if (isset(self::getErrorMessages()[$faultString])) {
                return array('code' => $faultString, 'message' => self::getErrorMessages()[$faultString]);
            }

            return $e->getMessage();
        }

        $response = $this->buildResponse($response);
        $response->setTara($countryCode);

        return $response;
    }

    /**
     *
     * @param \stdClass $data
     * @return \Stev\ListaFirmeBundle\Lib\Response
     */
    protected function buildResponse($data)
    {
        $response = new Response();

        $response->setNume($data->name);
        $response->setCui($data->vatNumber);
        $response->setAdresa($data->address);
        $response->setTva($data->valid);
        $response->setDataTva($data->requestDate);

        return $response;
    }

    private static function getErrorMessages()
    {
        return array(
            self::INVALID_INPUT => 'The provided CountryCode is invalid or the VAT number is empty',
            self::GLOBAL_MAX_CONCURRENT_REQ => 'Your Request for VAT validation has not been processed; the maximum number of concurrent requests has been reached. Please try again later.',
            self::MS_MAX_CONCURRENT_REQ => 'Your Request for VAT validation has not been processed; the maximum number of concurrent requests for this Member State has been reached. Please try again later.',
            self::SERVICE_UNAVAILABLE => 'An error was encountered either at the network level or the Web application level, try again later',
            self::MS_UNAVAILABLE => 'The application at the Member State is not replying or not available. Please refer to the Technical Information page to check the status of the requested Member State. Try again later',
            self::TIMEOUT => 'The application did not receive a reply within the allocated time period. Try again later.',
        );
    }

}
