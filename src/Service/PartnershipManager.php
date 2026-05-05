<?php

namespace App\Service;

use App\Entity\Partnership;

class PartnershipManager
{
    public function validate(Partnership $partnership): bool
    {
        if (trim((string) $partnership->getStatus()) === '') {
            throw new \InvalidArgumentException('Le statut du partenariat est obligatoire');
        }

        if ($partnership->getCompany() === null) {
            throw new \InvalidArgumentException('La societe source est obligatoire');
        }

        $establishedDate = $partnership->getEstablishedDate();
        $terminatedDate = $partnership->getTerminatedDate();

        if ($establishedDate !== null && $terminatedDate !== null && $terminatedDate <= $establishedDate) {
            throw new \InvalidArgumentException('La date de fin doit etre posterieure a la date de debut');
        }

        return true;
    }
}
