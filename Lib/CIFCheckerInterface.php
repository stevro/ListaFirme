<?php

/*
 * This code belongs to NIMA Software SRL | nimasoftware.com
 * For details contact contact@nimasoftware.com
 */

namespace Stev\ListaFirmeBundle\Lib;

/**
 *
 * @author stefan
 */
interface CIFCheckerInterface
{
    public function checkCompanyByCUI($cui);
    
    public function mockResponse($cui);
}