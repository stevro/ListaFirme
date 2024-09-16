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

    public function setLongName(string $longName);

    public function getCif();

    public function setCif(string $cif);

    public function getAddress();

    public function setAddress(?string $address);

    public function getCity();

    public function setCity(?string $city);

    public function getCountry();

    public function setCountry(string $country);

    public function setRegistrationNumber(?string $registrationNumber);

    public function getRegistrationNumber();

    public function setIsVatPayer(bool $isVatPayer);
}