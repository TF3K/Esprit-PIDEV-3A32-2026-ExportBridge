<?php

namespace App\Entity;

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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $trade_agreement = null;

    public function getTrade_agreement(): ?string
    {
        return $this->trade_agreement;
    }

    public function setTrade_agreement(?string $trade_agreement): self
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

    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'market')]
    private Collection $companies;

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        if (!$this->companies instanceof Collection) {
            $this->companies = new ArrayCollection();
        }
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->getCompanies()->contains($company)) {
            $this->getCompanies()->add($company);
        }
        return $this;
    }

    public function removeCompany(Company $company): self
    {
        $this->getCompanies()->removeElement($company);
        return $this;
    }

}
