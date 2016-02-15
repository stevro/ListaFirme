<?php

/*
 * This code belongs to NIMA Software SRL | nimasoftware.com
 * For details contact contact@nimasoftware.com
 */

namespace Stev\ListaFirmeBundle\Lib;

/**
 * Description of Response
 *
 * @author stefan
 */
class Response
{

    private $name;
    private $cui;
    private $nrInmatr;
    private $judet;
    private $localitate;
    private $tip;
    private $adresa;
    private $nr;
    private $stare;
    private $actualizat;
    private $tva;
    private $tvaIncasare;
    private $dataTva;

    public function getNume()
    {
        return $this->name;
    }

    public function getCui()
    {
        return $this->cui;
    }

    public function getNrInmatr()
    {
        return $this->nrInmatr;
    }

    public function getJudet()
    {
        return $this->judet;
    }

    public function getLocalitate()
    {
        return $this->localitate;
    }

    public function getTip()
    {
        return $this->tip;
    }

    public function getAdresa()
    {
        return $this->adresa;
    }

    public function getNr()
    {
        return $this->nr;
    }

    public function getStare()
    {
        return $this->stare;
    }

    public function getActualizat()
    {
        return $this->actualizat;
    }

    public function getTva()
    {
        return $this->tva;
    }

    public function getTvaIncasare()
    {
        return $this->tvaIncasare;
    }

    public function getDataTva()
    {
        return $this->dataTva;
    }

    public function setNume($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setCui($cui)
    {
        $this->cui = $cui;
        return $this;
    }

    public function setNrInmatr($nrInmatr)
    {
        $this->nrInmatr = $nrInmatr;
        return $this;
    }

    public function setJudet($judet)
    {
        $this->judet = $judet;
        return $this;
    }

    public function setLocalitate($localitate)
    {
        $this->localitate = $localitate;
        return $this;
    }

    public function setTip($tip)
    {
        $this->tip = $tip;
        return $this;
    }

    public function setAdresa($adresa)
    {
        $this->adresa = $adresa;
        return $this;
    }

    public function setNr($nr)
    {
        $this->nr = $nr;
        return $this;
    }

    public function setStare($stare)
    {
        $this->stare = $stare;
        return $this;
    }

    public function setActualizat($actualizat)
    {
        $this->actualizat = $actualizat;
        return $this;
    }

    public function setTva($tva)
    {
        $this->tva = $tva;
        return $this;
    }

    public function setTvaIncasare($tvaIncasare)
    {
        $this->tvaIncasare = $tvaIncasare;
        return $this;
    }

    public function setDataTva($dataTva)
    {
        $this->dataTva = $dataTva;
        return $this;
    }

    public function getFullAddress()
    {
        $address = $this->getJudet() . ' ';

        if (strlen($this->getLocalitate()) > 0) {
            $address .= $this->getLocalitate() . ' ';
        }

        if (strlen($this->getTip()) > 0) {
            $address .= $this->getTip() . ' ';
        }

        if (strlen($this->getAdresa()) > 0) {
            $address .= $this->getAdresa() . ' ';
        }

        if (strlen($this->getNr()) > 0) {
            $address .= $this->getNr() . ' ';
        }

        return rtrim($address);
    }

//
//
//    (object) array(
//                    'Nume' => $faker->company . ' ' . 'SRL',
//                    'CUI' => $cui,
//                    'NrInmatr' => 'J40/' . $faker->randomNumber(4) . '/2014',
//                    'Judet' => $faker->citySuffix,
//                    'Localitate' => $faker->city,
//                    'Tip' => 'Str',
//                    'Adresa' => $faker->address,
//                    'Nr' => $faker->randomNumber(2),
//                    'Stare' => 'Inregistrat de curand',
//                    'Actualizat' => $faker->date(),
//                    'TVA' => rand(0, 1),
//                    'TVAincasare' => rand(0, 1),
//                    'DataTVA' => $faker->date()
//        );
}
