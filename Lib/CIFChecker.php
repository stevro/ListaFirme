<?php

/*
 * This code belongs to NIMA Software SRL | nimasoftware.com
 * For details contact contact@nimasoftware.com
 */

namespace Stev\ListaFirmeBundle\Lib;

/**
 * Description of CIFChecker
 *
 * @author stefan
 */
class CIFChecker {

    const CHECKER_LISTA_FIRME = 'listaFirme';
    const CHECKER_MFIN = 'mFin';
    const CHECKER_OPEN_API = 'openApi';

    private $checker;
    private $logger;

    /**
     * @param string $cifChecker
     * @param string $username
     * @param string $password
     * @param bool $offline Set it to true if list firme is down or if you want to disable the check. It will make the check to return a mocked(dummy) response.
     * @param bool $enabled If you set it to false it will completly disable the checker.
     * @param LoggerInterface
     */
    public function __construct($cifChecker, $username, $password, $offline = false, $enabled = true, $pathToPhantom = null, \Psr\Log\LoggerInterface $logger) {
        $this->logger = $logger;
        switch ($cifChecker) {
            case self::CHECKER_LISTA_FIRME:
                $this->checker = new ListaFirme($username, $password, $offline, $enabled);
                break;
            case self::CHECKER_MFIN:
                $this->checker = new MFin($offline, $enabled, $pathToPhantom);
                break;
            case self::CHECKER_OPEN_API:
                $this->checker = new OpenAPI($offline, $enabled);
                break;
            default:
                throw new \InvalidArgumentException('You provided an invalid cifChecker ' . $cifChecker);
        }
    }

    public function checkCompanyByCUI($cui) {
        $response = $this->checker->checkCompanyByCUI($cui);

        if ($this->validateResponse($response, $cui)) {
            return $response;
        }

        return null;
    }

    private function validateResponse($response, $cui) {

        if (!$response instanceof Response) {
            return false;
        }

        if (!$response->getNume() || !$response->getCui() || !$response->getNrInmatr() || !$response->getFullAddress()) {

            $this->logger->critical('Unable to verify company CUI ' . $cui);
            $this->logger->critical('The response was ' . (string) $response);

            return false;
        }

        return true;
    }

}
