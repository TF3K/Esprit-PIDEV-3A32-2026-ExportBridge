<?php

namespace App\Service;

use App\Entity\Market;

class MarketManager
{
    /**
     * Validation métier d'un Market
     */
    public function validate(Market $market): bool
    {
        // 1. Nom obligatoire
        if (empty($market->getName())) {
            throw new \InvalidArgumentException('Le nom du marché est obligatoire');
        }

        // 2. country_code obligatoire
        if (empty($market->getCountryCode())) {
            throw new \InvalidArgumentException('Le code pays est obligatoire');
        }

        // 3. country_code doit avoir au moins 2 caractères
        if (strlen($market->getCountryCode()) < 2) {
            throw new \InvalidArgumentException('Le code pays doit contenir au moins 2 caractères');
        }

        // 4. is_eu doit être bool ou null (ok mais pas autre chose)
        if ($market->isEu() !== null && !is_bool($market->isEu())) {
            throw new \InvalidArgumentException('isEu doit être un booléen');
        }

        return true;
    }

    /**
     * Règle métier : un marché est considéré "UE actif"
     */
    public function isActiveEuMarket(Market $market): bool
    {
        return $market->isEu() === true;
    }
}