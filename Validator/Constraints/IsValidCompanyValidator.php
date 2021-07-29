<?php

/*
 *  Developed by Stefan Matei - stev.matei@gmail.com
 */

namespace Stev\ListaFirmeBundle\Validator\Constraints;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Description of IsValidCompanyValidator
 *
 * @author stefan
 */
class IsValidCompanyValidator extends ConstraintValidator {

    protected $listaFirme;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(\Stev\ListaFirmeBundle\Lib\CIFChecker $listaFirme, LoggerInterface $logger) {
        $this->listaFirme = $listaFirme;
    }

    public function validate($company, Constraint $constraint) {
        if (!$company instanceof \Stev\ListaFirmeBundle\Model\CompanyInterface) {
            throw new \RuntimeException('Your class must implement \Stev\ListaFirmeBundle\Model\CompanyInterface, instead you provided ' . get_class($company));
        }

        try {
            $companyVerification = $this->listaFirme->checkCompanyByCUI($company->getCif(), $company->getCountry());
        } catch (\Exception $e) {

            $this->logger->error($e->getMessage());

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
        if ($companyVerification->getLocalitate()) {
            $company->setCity($companyVerification->getLocalitate());
        }

        if($companyVerification->getNrInmatr()){
            $company->setRegistrationNumber($companyVerification->getNrInmatr());
        }
        if(is_bool($companyVerification->getTva())){
            $company->setIsVatPayer($companyVerification->getTva());
        }
    }

}
