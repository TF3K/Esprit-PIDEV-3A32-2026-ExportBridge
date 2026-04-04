<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProductRepository;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
class Product
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

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id')]
    private ?Company $company = null;

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;
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
    private ?string $hs_code = null;

    public function getHsCode(): ?string
    {
        return $this->hs_code;
    }

    public function setHsCode(?string $hs_code): self
    {
        $this->hs_code = $hs_code;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: ProductCategorie::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    private ?ProductCategorie $productCategorie = null;

    public function getProductCategorie(): ?ProductCategorie
    {
        return $this->productCategorie;
    }

    public function setProductCategorie(?ProductCategorie $productCategorie): self
    {
        $this->productCategorie = $productCategorie;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $quantity = null;

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $unit = null;

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $unit_price = null;

    public function getUnit_price(): ?float
    {
        return $this->unit_price;
    }

    public function setUnit_price(?float $unit_price): self
    {
        $this->unit_price = $unit_price;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $currency = null;

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $origin_criteria = null;

    public function getOrigin_criteria(): ?string
    {
        return $this->origin_criteria;
    }

    public function setOrigin_criteria(?string $origin_criteria): self
    {
        $this->origin_criteria = $origin_criteria;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    public function getCreated_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $last_updated = null;

    public function getLast_updated(): ?\DateTimeInterface
    {
        return $this->last_updated;
    }

    public function setLast_updated(\DateTimeInterface $last_updated): self
    {
        $this->last_updated = $last_updated;
        return $this;
    }

    public function getHsCode(): ?string
    {
        return $this->hs_code;
    }

    public function setHsCode(?string $hs_code): static
    {
        $this->hs_code = $hs_code;

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unit_price;
    }

    public function setUnitPrice(?string $unit_price): static
    {
        $this->unit_price = $unit_price;

        return $this;
    }

    public function getOriginCriteria(): ?string
    {
        return $this->origin_criteria;
    }

    public function setOriginCriteria(?string $origin_criteria): static
    {
        $this->origin_criteria = $origin_criteria;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getLastUpdated(): ?\DateTime
    {
        return $this->last_updated;
    }

    public function setLastUpdated(\DateTime $last_updated): static
    {
        $this->last_updated = $last_updated;

        return $this;
    }

}
