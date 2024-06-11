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

/**
 * @Annotation
 */
class IsValidCompany extends Constraint
{

    public $message = 'CIF-ul %string% nu este valid.';
    public $details = '%string%';

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'is_valid_company';
    }

}