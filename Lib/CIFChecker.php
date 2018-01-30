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
    const CHECKER_ANAF = 'anaf';

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
    public function __construct($cifChecker, $username, $password, $offline = false, $enabled = true, $pathToPhantom = null, \Psr\Log\LoggerInterface $logger, $apiKey = null) {
        $this->logger = $logger;
        switch ($cifChecker) {
            case self::CHECKER_LISTA_FIRME:
                $this->checker = new ListaFirme($username, $password, $offline, $enabled);
                break;
            case self::CHECKER_MFIN:
                $this->checker = new MFin($offline, $enabled, $pathToPhantom);
                break;
            case self::CHECKER_OPEN_API:
                $this->checker = new OpenAPI($offline, $enabled, $apiKey);

                $fallback = new Anaf($offline, $enabled);
                $this->checker->addFallback($fallback);

                $fallback1 = new OpenAPILegacy($offline, $enabled);
                $this->checker->addFallback($fallback1);
                break;
            case self::CHECKER_ANAF:
                $this->checker = new Anaf($offline, $enabled);

                $fallback = new OpenAPILegacy($offline, $enabled);
                $this->checker->addFallback($fallback);
                break;
            default:
                throw new \InvalidArgumentException('You provided an invalid cifChecker ' . $cifChecker);
        }
    }

    public function checkCompanyByCUI($cui) {
        $response = $this->checker->checkCompanyByCUI($cui);

        $this->logger->info("Calling main checker " . $this->checker->getCheckerName());

        if ($this->validateResponse($response, $cui)) {
            return $response;
        }

        /* @var $fallback CIFCheckerInterface */
        foreach ($this->checker->getFallbacks() as $fallback) {

            $this->logger->info("Calling fallback checker " . $fallback->getCheckerName());

            $response = $fallback->checkCompanyByCUI($cui);

            if ($this->validateResponse($response, $cui)) {
                return $response;
            }
        }

        return null;
    }

    private function validateResponse($response, $cui) {

        $this->logger->info('Validating response');
        $this->logger->debug(serialize($response));

        if (!$response instanceof Response) {
            $this->logger->critical('Unable to verify company CUI ' . $cui);
            $this->logger->critical('The response was ' . (string) $response);

            return false;
        }

        if (!$response->getNume() || !$response->getCui()) {

            $this->logger->critical('Unable to find all details of company CUI ' . $cui);
            $this->logger->critical('The response was ' . (string) $response);

            return false;
        }

        return true;
    }

}
