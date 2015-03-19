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
class CompanyInterface
{

    public function getLongName();

    public function setLongName();

    public function getCif();

    public function setCif();

    public function getAddress();

    public function setAddress();

    public function getCity();

    public function setCity();

    public function getCountry();

    public function setCountry();

    public function setRegistrationNumber();

    public function getRegistrationNumber();
}