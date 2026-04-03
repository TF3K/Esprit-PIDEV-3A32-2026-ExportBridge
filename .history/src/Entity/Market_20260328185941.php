<?php

namespace App\Entity;

use App\Repository\MarketRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarketRepository::class)]
class Market
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $countryCode = null;

    #[ORM\Column(length: 255)]
    private ?string $namr = null;

    #[ORM\Column(length: 255)]
    private ?string $region = null;

    #[ORM\Column]
    private ?bool $isEu = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $tradeAgreement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->namr;
    }

    public function setNamr(string $namr): static
    {
        $this->namr = $namr;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function isEu(): ?bool
    {
        return $this->isEu;
    }

    public function setIsEu(bool $isEu): static
    {
        $this->isEu = $isEu;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTradeAgreement(): ?string
    {
        return $this->tradeAgreement;
    }

    public function setTradeAgreement(string $tradeAgreement): static
    {
        $this->tradeAgreement = $tradeAgreement;

        return $this;
    }
}
