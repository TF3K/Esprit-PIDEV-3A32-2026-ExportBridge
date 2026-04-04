<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CertificateRequirementRepository;

#[ORM\Entity(repositoryClass: CertificateRequirementRepository::class)]
#[ORM\Table(name: 'certificate_requirements')]
class CertificateRequirement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Market::class, inversedBy: 'certificateRequirements')]
    #[ORM\JoinColumn(name: 'market_id', referencedColumnName: 'id')]
    private ?Market $market = null;

    public function getMarket(): ?Market
    {
        return $this->market;
    }

    public function setMarket(?Market $market): self
    {
        $this->market = $market;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $product_category = null;

    public function getProductCategory(): ?string
    {
        return $this->product_category;
    }

    public function setProductCategory(string $product_category): self
    {
        $this->product_category = $product_category;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $certificate_type = null;

    public function getCertificate_type(): ?string
    {
        return $this->certificate_type;
    }

    public function setCertificate_type(string $certificate_type): self
    {
        $this->certificate_type = $certificate_type;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $mandatory = null;

    public function isMandatory(): ?bool
    {
        return $this->mandatory;
    }

    public function setMandatory(?bool $mandatory): self
    {
        $this->mandatory = $mandatory;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getProductCategory(): ?string
    {
        return $this->product_category;
    }

    public function setProductCategory(string $product_category): static
    {
        $this->product_category = $product_category;

        return $this;
    }

    public function getCertificateType(): ?string
    {
        return $this->certificate_type;
    }

    public function setCertificateType(string $certificate_type): static
    {
        $this->certificate_type = $certificate_type;

        return $this;
    }

}
