<?php

/*
 *  Developed by Stefan Matei - stev.matei@gmail.com
 */

namespace Stev\ListaFirmeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Description of IsValidCompanyValidator
 *
 * @author stefan
 */
class IsValidCompanyValidator extends ConstraintValidator
{

    protected $listaFirme;

    public function __construct(\Stev\ListaFirmeBundle\Lib\CIFChecker $listaFirme)
    {
        $this->listaFirme = $listaFirme;
    }

    public function validate($company, Constraint $constraint)
    {
        if (!$company instanceof \Stev\ListaFirmeBundle\Model\CompanyInterface) {
            throw new \RuntimeException('Your class must implement \Stev\ListaFirmeBundle\Model\CompanyInterface, instead you provided ' . get_class($company));
        }

        try {
            if (!in_array(strtoupper($company->getCountry()), array('RO', 'ROMANIA'))) {
                return;
            }
            $companyVerification = $this->listaFirme->checkCompanyByCUI($company->getCif());
        } catch (\Exception $e) {
            $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $company->getCif())
                    ->addViolation();

            return;
        }

        if (!$companyVerification instanceof \Stev\ListaFirmeBundle\Lib\Response) {
            return;
        }

        $company->setLongName($companyVerification->getNume());
        $company->setAddress($companyVerification->getFullAddress());
        $company->setCity($companyVerification->getLocalitate() ? $companyVerification->getLocalitate() : '');
        $company->setCountry('RO');
        $company->setRegistrationNumber($companyVerification->getNrInmatr());
    }

}
