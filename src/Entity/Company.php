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
    // -------------------------------------------------------------------------
    // ID
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Basic Info
    // -------------------------------------------------------------------------

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $company_name = null;

    public function getCompanyName(): ?string
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

    // -------------------------------------------------------------------------
    // Contact
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Status
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Contract
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Timestamps
    // -------------------------------------------------------------------------

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

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $last_updated = null;

    public function getLastUpdated(): ?\DateTimeInterface
    {
        return $this->last_updated;
    }

    public function setLastUpdated(\DateTimeInterface $last_updated): self
    {
        $this->last_updated = $last_updated;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    /**
     * Single manager (ManyToOne)
     */
    #[ORM\ManyToOne(targetEntity: Manager::class, inversedBy: 'companies')]
    #[ORM\JoinColumn(name: 'company_manager_id', referencedColumnName: 'id')]
    private ?Manager $manager = null;

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function setManager(?Manager $manager): self
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * Multiple managers (OneToMany)
     *
     * @var Collection<int, Manager>
     */
    #[ORM\OneToMany(targetEntity: Manager::class, mappedBy: 'company')]
    private Collection $managers;

    /**
     * @return Collection<int, Manager>
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    public function addManager(Manager $manager): self
    {
        if (!$this->managers->contains($manager)) {
            $this->managers->add($manager);
        }
        return $this;
    }

    public function removeManager(Manager $manager): self
    {
        $this->managers->removeElement($manager);
        return $this;
    }

    /**
     * Contact History (OneToMany)
     * ✅ FIXED Doctrine Doctor: mappedBy corrigé de 'contactHistorys' → 'company'
     * car dans ContactHistory.php la propriété s'appelle $company avec inversedBy: 'contactHistorys'
     * On doit aligner : mappedBy ici = nom de la propriété dans ContactHistory = 'company'
     * ET renommer inversedBy dans ContactHistory = 'contactHistory' (sans s)
     *
     * @var Collection<int, ContactHistory>
     */
    #[ORM\OneToMany(targetEntity: ContactHistory::class, mappedBy: 'company')]
    private Collection $contactHistory;

    /**
     * @return Collection<int, ContactHistory>
     */
    public function getContactHistory(): Collection
    {
        return $this->contactHistory;
    }

    public function addContactHistory(ContactHistory $contactHistory): self
    {
        if (!$this->contactHistory->contains($contactHistory)) {
            $this->contactHistory->add($contactHistory);
        }
        return $this;
    }

    public function removeContactHistory(ContactHistory $contactHistory): self
    {
        $this->contactHistory->removeElement($contactHistory);
        return $this;
    }

    /**
     * ✅ FIXED Doctrine Doctor: suppression de la relation certificates
     * Certificate.php utilise $company_id (entier brut) et n'a PAS de ManyToOne vers Company
     * La relation ManyToOne existe côté Certificate, donc l'inverse doit être exposé ici
     * pour garder le mapping Doctrine cohérent.
     */
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

    public function getPartnership(): ?Partnership
    {
        return $this->partnership;
    }

    public function setPartnership(?Partnership $partnership): self
    {
        $this->partnership = $partnership;
        return $this;
    }

    /**
     * Products (OneToMany)
     *
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'company')]
    private Collection $products;

    public function __construct()
    {
        $this->contactHistory = new ArrayCollection();
        $this->certificates = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->managers = new ArrayCollection();
    }
    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }
        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);
        return $this;
    }

    /**
     * Market (ManyToOne)
     */
    #[ORM\ManyToOne(targetEntity: Market::class, inversedBy: 'companies')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Market $market = null;

    public function getMarket(): ?Market
    {
        return $this->market;
    }

    public function setMarket(?Market $market): static
    {
        $this->market = $market;
        return $this;
    }
}
