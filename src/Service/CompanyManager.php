<?php

namespace App\Service;

use App\Entity\Company;

class CompanyManager
{
    public function validate(Company $company): bool
    {
        if (empty($company->getCompanyName())) {
            throw new \InvalidArgumentException('Le nom de la société est obligatoire');
        }

        if (
            $company->getContactEmail() &&
            !filter_var($company->getContactEmail(), FILTER_VALIDATE_EMAIL)
        ) {
            throw new \InvalidArgumentException('Email invalide');
        }

        if (
            $company->getRating() !== null &&
            ($company->getRating() < 1 || $company->getRating() > 5)
        ) {
            throw new \InvalidArgumentException('Rating invalide');
        }

        return true;
    }
}