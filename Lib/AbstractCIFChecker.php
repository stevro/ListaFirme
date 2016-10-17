<?php

/*
 * This code belongs to NIMA Software SRL | nimasoftware.com
 * For details contact contact@nimasoftware.com
 */

namespace Stev\ListaFirmeBundle\Lib;

use GuzzleHttp\Client;

/**
 * Description of AbstractCIFChecker
 *
 * @author stefan
 */
abstract class AbstractCIFChecker implements CIFCheckerInterface
{

    protected $offline = false;
    protected $enabled = false;

    /**
     *
     * @var \SplObjectStorage
     */
    private $fallbacks;

    /**
     * 
     * @param bool $offline Set it to true if list firme is down or if you want to disable the check. It will make the check to return a mocked(dummy) response.
     * @param bool $enabled If you set it to false it will completly disable the checker.
     */
    public function __construct($offline, $enabled)
    {
        $this->offline = $offline;
        $this->enabled = $enabled;
        $this->fallbacks = new \SplObjectStorage();
    }

    public function addFallback(CIFCheckerInterface $fallbackCIFChecker)
    {
        $this->fallbacks->attach($fallbackCIFChecker);
    }

    public function removeFallback(CIFCheckerInterface $fallbackCIFChecker)
    {
        $this->fallbacks->detach($fallbackCIFChecker);
    }

    public function getFallbacks()
    {
        return $this->fallbacks;
    }

    abstract protected function check($cui);

    /**
     * 
     * @param string $cui
     * @return null | Response
     */
    public function checkCompanyByCUI($cui)
    {

        if (false === $this->enabled) {
            return null;
        }

        if (true === $this->offline) {
            return $this->mockResponse($cui);
        }

        $prefix = 'RO';
        if (0 === strpos($cui, $prefix)) {
            $cui = substr($cui, strlen($prefix));
        }

        return $this->check($cui);
    }

    /**
     * 
     * @param string $cui
     * @return \Stev\ListaFirmeBundle\Lib\Response
     */
    public function mockResponse($cui)
    {
        $faker = \Faker\Factory::create();
        $faker->addProvider(new \CompanyNameGenerator\FakerProvider($faker));

        $response = new Response();

        $response->setNume($faker->company . ' ' . 'SRL');
        $response->setCui($cui);
        $response->setNrInmatr('J40/' . $faker->randomNumber(4) . '/2014');
        $response->setJudet($faker->citySuffix);
        $response->setLocalitate($faker->city);
        $response->setTip('Str');
        $response->setAdresa($faker->address);
        $response->setNr($faker->randomNumber(2));
        $response->setStare('Inregistrat de curand');
        $response->setActualizat($faker->date());
        $response->setTva(rand(0, 1));
        $response->setTvaIncasare(rand(0, 1));
        $response->setDataTva($faker->date());

        return $response;
    }

    abstract protected function buildResponse($data);
}
