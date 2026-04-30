<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\CompanyRepository;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\Table(name: 'companies')]
class Company
{
    // ---------------------------------------------------------------------
    // ID
    // ---------------------------------------------------------------------

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    // ---------------------------------------------------------------------
    // BASIC INFO
    // ---------------------------------------------------------------------

    #[ORM\Column(type: 'string', nullable: false)]
    private string $company_name = '';

    public function getCompanyName(): string
    {
        return $this->company_name;
    }
    public function setCompanyName(string $company_name): self
    {
        $this->company_name = $company_name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $domain = null;

    public function getDomain(): ?string
    {
        return $this->domain;
    }
    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $tax_number = null;

    public function getTaxNumber(): ?string
    {
        return $this->tax_number;
    }
    public function setTaxNumber(?string $tax_number): self
    {
        $this->tax_number = $tax_number;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $registration_number = null;

    public function getRegistrationNumber(): ?string
    {
        return $this->registration_number;
    }
    public function setRegistrationNumber(?string $registration_number): self
    {
        $this->registration_number = $registration_number;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $country = null;

    public function getCountry(): ?string
    {
        return $this->country;
    }
    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    public function getAddress(): ?string
    {
        return $this->address;
    }
    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    // ---------------------------------------------------------------------
    // CONTACT
    // ---------------------------------------------------------------------

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $contact_email = null;

    public function getContactEmail(): ?string
    {
        return $this->contact_email;
    }
    public function setContactEmail(?string $contact_email): self
    {
        $this->contact_email = $contact_email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $contact_phone = null;

    public function getContactPhone(): ?string
    {
        return $this->contact_phone;
    }
    public function setContactPhone(?string $contact_phone): self
    {
        $this->contact_phone = $contact_phone;
        return $this;
    }

    // ---------------------------------------------------------------------
    // STATUS
    // ---------------------------------------------------------------------

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rating = null;

    public function getRating(): ?int
    {
        return $this->rating;
    }
    public function setRating(?int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $warnings = null;

    public function getWarnings(): ?int
    {
        return $this->warnings;
    }
    public function setWarnings(?int $warnings): self
    {
        $this->warnings = $warnings;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $is_banned = null;

    public function isBanned(): ?bool
    {
        return $this->is_banned;
    }
    public function setIsBanned(?bool $is_banned): self
    {
        $this->is_banned = $is_banned;
        return $this;
    }

    // ---------------------------------------------------------------------
    // CONTRACT
    // ---------------------------------------------------------------------

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $contractHash = null;

    public function getContractHash(): ?string
    {
        return $this->contractHash;
    }
    public function setContractHash(?string $contractHash): self
    {
        $this->contractHash = $contractHash;
        return $this;
    }

    // ---------------------------------------------------------------------
    // DATES
    // ---------------------------------------------------------------------

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $last_updated;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created_at;
    }
    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getLastUpdated(): \DateTimeInterface
    {
        return $this->last_updated;
    }
    public function setLastUpdated(\DateTimeInterface $last_updated): self
    {
        $this->last_updated = $last_updated;
        return $this;
    }

    // ---------------------------------------------------------------------
    // RELATIONS
    // ---------------------------------------------------------------------

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'company')]
    private Collection $products;

    /**
     * @var Collection<int, ContactHistory>
     */
    #[ORM\OneToMany(targetEntity: ContactHistory::class, mappedBy: 'company')]
    private Collection $contactHistory;

    #[ORM\ManyToOne(targetEntity: Manager::class, inversedBy: 'companies')]
    private ?Manager $companyManager = null;

    public function getCompanyManager(): ?Manager
    {
        return $this->companyManager;
    }
    public function setCompanyManager(?Manager $companyManager): self
    {
        $this->companyManager = $companyManager;
        return $this;
    }

    /**
     * @var Collection<int, Certificate>
     */
    #[ORM\OneToMany(targetEntity: Certificate::class, mappedBy: 'company')]
    private Collection $certificates;

    /**
     * @return Collection<int, Certificate>
     */
    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(Certificate $certificate): self
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates->add($certificate);
        }

        return $this;
    }

    public function removeCertificate(Certificate $certificate): self
    {
        $this->certificates->removeElement($certificate);

        return $this;
    }

    /**
     * Partnership (OneToOne)
     */
    #[ORM\OneToOne(targetEntity: Partnership::class, mappedBy: 'company', cascade: ['persist', 'remove'])]
    private ?Partnership $partnership = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->contactHistory = new ArrayCollection();
        $this->certificates = new ArrayCollection();
        $this->created_at = new \DateTime();
        $this->last_updated = new \DateTime();
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @return Collection<int, ContactHistory>
     */
    public function getContactHistory(): Collection
    {
        return $this->contactHistory;
    }
}
