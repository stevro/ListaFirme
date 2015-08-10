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

    public function __construct(\Stev\ListaFirmeBundle\Lib\ListaFirme $listaFirme)
    {
        $this->listaFirme = $listaFirme;
    }

    public function validate($company, Constraint $constraint)
    {
        if (!$company instanceof \Stev\ListaFirmeBundle\Model\CompanyInterface) {
            throw new \RuntimeException('Your class must implement \Stev\ListaFirmeBundle\Model\CompanyInterface, instead you provided ' . get_class($company));
        }

        try {
            if(!in_array(strtoupper($company->getCountry()), array('RO', 'ROMANIA'))){
                return;
            }
            $companyVerification = $this->listaFirme->checkCompanyByCUI($company->getCif());
        } catch (\Exception $e) {
            $this->context->buildViolation($constraint->message)
                    ->setParameter('%string%', $company->getCif())
                    ->addViolation();
//            $this->context->buildViolation($constraint->details)
//                    ->setParameter('%string%', $e->getMessage())
//                    ->addViolation();

            return;
        }

//        {"Raspuns":"valid","Nume":"BORG DESIGN SRL","CUI":"14837428","NrInmatr":"J40/8118/2002",
//"Judet":"BUCURESTI","Localitate":"BUCURESTI","Tip":"STR.","Adresa":"DEMOCRATIEI","Nr":"4",
//"Stare":"INREGISTRAT DIN DATA 25 AUGUST 2006","Actualizat":"2015/01/14","TVA":"1",
//"TVAincasare":"0","DataTVA":"2015/01/14"}

        $company->setLongName($companyVerification->Nume);
        $address = $companyVerification->Judet .
                ' ' . $companyVerification->Localitate .
                ' ' . $companyVerification->Tip .
                ' ' . $companyVerification->Adresa .
                ' ' . $companyVerification->Nr;

        $company->setAddress($address);
        $company->setCity($companyVerification->Localitate);
        $company->setCountry('RO');
        $company->setRegistrationNumber($companyVerification->NrInmatr);
    }

}