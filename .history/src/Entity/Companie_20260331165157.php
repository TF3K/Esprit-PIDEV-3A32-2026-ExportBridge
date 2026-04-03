<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CompanieRepository;

#[ORM\Entity(repositoryClass: CompanieRepository::class)]
#[ORM\Table(name: 'companies')]
class Companie
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
    private ?string $company_name = null;

    public function getCompany_name(): ?string
    {
        return $this->company_name;
    }

    public function setCompany_name(string $company_name): self
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

    public function getTax_number(): ?string
    {
        return $this->tax_number;
    }

    public function setTax_number(?string $tax_number): self
    {
        $this->tax_number = $tax_number;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $registration_number = null;

    public function getRegistration_number(): ?string
    {
        return $this->registration_number;
    }

    public function setRegistration_number(?string $registration_number): self
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $contact_email = null;

    public function getContact_email(): ?string
    {
        return $this->contact_email;
    }

    public function setContact_email(?string $contact_email): self
    {
        $this->contact_email = $contact_email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $contact_phone = null;

    public function getContact_phone(): ?string
    {
        return $this->contact_phone;
    }

    public function setContact_phone(?string $contact_phone): self
    {
        $this->contact_phone = $contact_phone;
        return $this;
    }

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

    public function is_banned(): ?bool
    {
        return $this->is_banned;
    }

    public function setIs_banned(?bool $is_banned): self
    {
        $this->is_banned = $is_banned;
        return $this;
    }

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

    #[ORM\OneToMany(targetEntity: Certificate::class, mappedBy: 'companie')]
    private Collection $certificates;

    /**
     * @return Collection<int, Certificate>
     */
    public function getCertificates(): Collection
    {
        if (!$this->certificates instanceof Collection) {
            $this->certificates = new ArrayCollection();
        }
        return $this->certificates;
    }

    public function addCertificate(Certificate $certificate): self
    {
        if (!$this->getCertificates()->contains($certificate)) {
            $this->getCertificates()->add($certificate);
        }
        return $this;
    }

    public function removeCertificate(Certificate $certificate): self
    {
        $this->getCertificates()->removeElement($certificate);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ContactHistory::class, mappedBy: 'companie')]
    private Collection $contactHistorys;

    /**
     * @return Collection<int, ContactHistory>
     */
    public function getContactHistorys(): Collection
    {
        if (!$this->contactHistorys instanceof Collection) {
            $this->contactHistorys = new ArrayCollection();
        }
        return $this->contactHistorys;
    }

    public function addContactHistory(ContactHistory $contactHistory): self
    {
        if (!$this->getContactHistorys()->contains($contactHistory)) {
            $this->getContactHistorys()->add($contactHistory);
        }
        return $this;
    }

    public function removeContactHistory(ContactHistory $contactHistory): self
    {
        $this->getContactHistorys()->removeElement($contactHistory);
        return $this;
    }
    

    public function addContactHistory(ContactHistory $contactHistory): self
    {
        if (!$this->getContactHistorys()->contains($contactHistory)) {
            $this->getContactHistorys()->add($contactHistory);
        }
        return $this;
    }

    public function removeContactHistory(ContactHistory $contactHistory): self
    {
        $this->getContactHistorys()->removeElement($contactHistory);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Manager::class, mappedBy: 'companie')]
    private Collection $managers;

    /**
     * @return Collection<int, Manager>
     */
    public function getManagers(): Collection
    {
        if (!$this->managers instanceof Collection) {
            $this->managers = new ArrayCollection();
        }
        return $this->managers;
    }

    public function addManager(Manager $manager): self
    {
        if (!$this->getManagers()->contains($manager)) {
            $this->getManagers()->add($manager);
        }
        return $this;
    }

    public function removeManager(Manager $manager): self
    {
        $this->getManagers()->removeElement($manager);
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Partnership::class, mappedBy: 'companie')]
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

    #[ORM\OneToOne(targetEntity: Partnership::class, mappedBy: 'companie')]
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

    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'companie')]
    private Collection $products;

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        if (!$this->products instanceof Collection) {
            $this->products = new ArrayCollection();
        }
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->getProducts()->contains($product)) {
            $this->getProducts()->add($product);
        }
        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->getProducts()->removeElement($product);
        return $this;
    }

}
