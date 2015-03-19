<?php

/*
 *  Developed by Stefan Matei - stev.matei@gmail.com
 */

namespace Stev\ListaFirmeBundle\Model;

/**
 * Description of CompanyInterface
 *
 * @author stefan
 */
interface CompanyInterface
{

    public function getLongName();

    public function setLongName($longName);

    public function getCif();

    public function setCif($cif);

    public function getAddress();

    public function setAddress($address);

    public function getCity();

    public function setCity($city);

    public function getCountry();

    public function setCountry($country);

    public function setRegistrationNumber($registrationNumber);

    public function getRegistrationNumber();
}