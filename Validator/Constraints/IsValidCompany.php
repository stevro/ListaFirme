<?php

/*
 *  Developed by Stefan Matei - stev.matei@gmail.com
 */

namespace Stev\ListaFirmeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Description of IsValidCompany
 *
 * @author stefan
 */
class IsValidCompany extends Constraint
{

    public $message = 'The CIF %string% you provided must be valid.';
    public $details = '%string%';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'is_valid_company';
    }

}