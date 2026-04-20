<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\MarketRepository;

#[ORM\Entity(repositoryClass: MarketRepository::class)]
#[ORM\Table(name: 'markets')]
class Market
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $country_code = null;

    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    public function setCountryCode(string $country_code): self
    {
        $this->country_code = $country_code;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $region = null;

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $is_eu = null;

    public function isEu(): ?bool
    {
        return $this->is_eu;
    }

    public function setIsEu(?bool $is_eu): self
    {
        $this->is_eu = $is_eu;
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

    #[ORM\OneToMany(mappedBy: 'market', targetEntity: Companie::class)]
private Collection $companies;

// In __construct()
$this->companies = new ArrayCollection();

// Getter
public function getCompanies(): Collection
{
    return $this->companies;
}

// Adder
public function addCompany(Companie $company): static
{
    if (!$this->companies->contains($company)) {
        $this->companies->add($company);
        $company->setMarket($this);
    }
    return $this;
}

// Remover
public function removeCompany(Companie $company): static
{
    if ($this->companies->removeElement($company)) {
        if ($company->getMarket() === $this) {
            $company->setMarket(null);
        }
    }
    return $this;
}

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $trade_agreement = null;

    public function getTradeAgreement(): ?string
    {
        return $this->trade_agreement;
    }

    public function setTradeAgreement(?string $trade_agreement): self
    {
        $this->trade_agreement = $trade_agreement;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: CertificateRequirement::class, mappedBy: 'market')]
    private Collection $certificateRequirements;

    public function __construct()
    {
        $this->certificateRequirements = new ArrayCollection();
    }

    /**
     * @return Collection<int, CertificateRequirement>
     */
    public function getCertificateRequirements(): Collection
    {
        if (!$this->certificateRequirements instanceof Collection) {
            $this->certificateRequirements = new ArrayCollection();
        }
        return $this->certificateRequirements;
    }

    public function addCertificateRequirement(CertificateRequirement $certificateRequirement): self
    {
        if (!$this->getCertificateRequirements()->contains($certificateRequirement)) {
            $this->getCertificateRequirements()->add($certificateRequirement);
        }
        return $this;
    }

    public function removeCertificateRequirement(CertificateRequirement $certificateRequirement): self
    {
        $this->getCertificateRequirements()->removeElement($certificateRequirement);
        return $this;
    }

}
