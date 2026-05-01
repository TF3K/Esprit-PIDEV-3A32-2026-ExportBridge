<?php

namespace App\Service;

use App\Entity\Certificate;

class CertificateManager
{
    public function validate(Certificate $certificate): bool
    {
        if (trim((string) $certificate->getCertificateNumber()) === '') {
            throw new \InvalidArgumentException('Le numero du certificat est obligatoire');
        }

        if (trim((string) $certificate->getType()) === '') {
            throw new \InvalidArgumentException('Le type du certificat est obligatoire');
        }

        $issueDate = $certificate->getIssueDate();
        $expiryDate = $certificate->getExpiryDate();

        if ($issueDate !== null && $expiryDate !== null && $expiryDate <= $issueDate) {
            throw new \InvalidArgumentException('La date d expiration doit etre posterieure a la date d emission');
        }

        return true;
    }
}
