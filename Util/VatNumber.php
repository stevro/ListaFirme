<?php

/*
 * This code belongs to NIMA Software SRL | nimasoftware.com
 * For details contact contact@nimasoftware.com
 */

namespace Stev\ListaFirmeBundle\Util;

/**
 * Description of VatNumber
 *
 * @author stefan
 */
class VatNumber
{

    public static function hasPrefix($vatNumber)
    {
        return preg_match('/^[a-zA-Z]{2,3}[0-9a-zA-Z]*$/', $vatNumber);
    }

    public static function clean($vatNumber)
    {
        return strtoupper(preg_replace("/[^a-zA-Z0-9]/", "", $vatNumber));
    }

}
