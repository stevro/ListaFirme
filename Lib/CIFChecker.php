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
class CIFChecker
{

    const CHECKER_LISTA_FIRME = 'listaFirme';
    const CHECKER_OPEN_API = 'openApi';
    const CHECKER_ANAF = 'anaf';
    const CHECKER_VIES = 'vies';

    /**
     *
     * @var CIFCheckerInterface
     */
    private $checker;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    private $options;

    /**
     * @param string $cifChecker
     * @param string $username
     * @param string $password
     * @param bool $offline Set it to true if list firme is down or if you want to disable the check. It will make the check to return a mocked(dummy) response.
     * @param bool $enabled If you set it to false it will completly disable the checker.
     * @param LoggerInterface
     */
    public function __construct($cifChecker, $username, $password, \Psr\Log\LoggerInterface $logger, $offline = false, $enabled = true, $apiKey = null)
    {
        $this->logger = $logger;

        $this->options = [
            'username' => $username,
            'password' => $password,
            'offline' => $offline,
            'enabled' => $enabled,
            'logger' => $logger,
            'apiKey' => $apiKey,
        ];

        switch ($cifChecker) {
            case self::CHECKER_LISTA_FIRME:
                $this->setupCheckers(array(self::CHECKER_LISTA_FIRME, self::CHECKER_VIES));
                break;
            case self::CHECKER_OPEN_API:
                $this->setupCheckers(array(self::CHECKER_OPEN_API, self::CHECKER_VIES, self::CHECKER_ANAF));
                break;
            case self::CHECKER_ANAF:
                $this->setupCheckers(array(self::CHECKER_ANAF));
                break;
            case self::CHECKER_VIES:
                $this->setupCheckers(array(self::CHECKER_VIES, self::CHECKER_OPEN_API));
                break;
            default:
                throw new \InvalidArgumentException('You provided an invalid cifChecker ' . $cifChecker);
        }

        $fallback = new OpenAPILegacy($offline, $enabled);
        $this->checker->addFallback($fallback);
    }

    /**
     *
     * @param array $checkers
     * @param array $options
     * [
      'username' => string,
      'password' => string,
      'offline' => bool,
      'enabled' => bool,
      'logger' => \Psr\Log\LoggerInterface,
      'apiKey' => string,
      ]
     */
    public function setupCheckers(array $checkers, array $options = array())
    {
        $this->checker = null;

        $options = array_merge($this->options, $options);

        $primary = array_shift($checkers);

        $this->checker = self::createCheckerInstance($primary, $options);

        foreach ($checkers as $fallback) {
            $fallbackChecker = self::createCheckerInstance($fallback, $options);
            $this->checker->addFallback($fallbackChecker);
        }
    }

    /**
     *
     * @param string $checker
     * @param array $options
     * @return CIFCheckerInterface
     * @throws \InvalidArgumentException
     */
    private static function createCheckerInstance($checker, array $options)
    {
        switch ($checker) {
            case self::CHECKER_LISTA_FIRME:
                return new ListaFirme($options['username'], $options['password'], $options['offline'], $options['enabled']);
                break;
            case self::CHECKER_OPEN_API:
                return new OpenAPI($options['apiKey'], $options['offline'], $options['enabled']);
                break;
            case self::CHECKER_ANAF:
                return new Anaf($options['offline'], $options['enabled']);
                break;
            case self::CHECKER_VIES:
                return new Vies($options['logger'], $options['offline'], $options['enabled']);
                break;
            default:
                throw new \InvalidArgumentException('You provided an invalid cifChecker ' . $checker);
        }
    }

    public function checkCompanyByCUI($cui, $countryCode = null)
    {

        $response = $this->checker->checkCompanyByCUI($cui, $countryCode);

        $this->logger->info("Calling main checker " . $this->checker->getCheckerName());

        if ($this->validateResponse($response, $cui)) {
            return $response;
        }

        /* @var $fallback CIFCheckerInterface */
        foreach ($this->checker->getFallbacks() as $fallback) {

            $this->logger->info("Calling fallback checker " . $fallback->getCheckerName());

            $response = $fallback->checkCompanyByCUI($cui, $countryCode);

            if ($this->validateResponse($response, $cui)) {
                return $response;
            }
        }

        return null;
    }

    private function validateResponse($response, $cui)
    {

        $this->logger->info('Validating response');
        $this->logger->debug(serialize($response));

        if (!$response instanceof Response) {
            $this->logger->critical('Unable to verify company CUI ' . $cui);
            $this->logger->critical('The response was ' . serialize($response));

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
