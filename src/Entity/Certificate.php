<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CertificateRepository;

#[ORM\Entity(repositoryClass: CertificateRepository::class)]
#[ORM\Table(name: 'certificates')]
class Certificate
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

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'certificates')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
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
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $certificate_number = null;

    public function getCertificateNumber(): ?string
    {
        return $this->certificate_number;
    }

    public function setCertificateNumber(string $certificate_number): self
    {
        $this->certificate_number = $certificate_number;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $issue_date = null;

    public function getIssueDate(): ?\DateTimeInterface
    {
        return $this->issue_date;
    }

    public function setIssueDate(?\DateTimeInterface $issue_date): self
    {
        $this->issue_date = $issue_date;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $expiry_date = null;

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiry_date;
    }

    public function setExpiryDate(?\DateTimeInterface $expiry_date): self
    {
        $this->expiry_date = $expiry_date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $status = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $country_of_origin = null;

    public function getCountryOfOrigin(): ?string
    {
        return $this->country_of_origin;
    }

    public function setCountryOfOrigin(?string $country_of_origin): self
    {
        $this->country_of_origin = $country_of_origin;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $issuing_authority = null;

    public function getIssuingAuthority(): ?string
    {
        return $this->issuing_authority;
    }

    public function setIssuingAuthority(?string $issuing_authority): self
    {
        $this->issuing_authority = $issuing_authority;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $document_file = null;

    public function getDocumentFile(): ?string
    {
        return $this->document_file;
    }

    public function setDocumentFile(?string $document_file): self
    {
        $this->document_file = $document_file;
        return $this;
    }


    #[ORM\ManyToOne(targetEntity: Partnership::class, inversedBy: 'certificates')]
    #[ORM\JoinColumn(name: 'partnership_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
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

    #[ORM\OneToMany(targetEntity: Signature::class, mappedBy: 'certificate')]
    private Collection $signatures;

    public function __construct()
    {
        $this->signatures = new ArrayCollection();
    }

    /**
     * @return Collection<int, Signature>
     */
    public function getSignatures(): Collection
    {
        if (!$this->signatures instanceof Collection) {
            $this->signatures = new ArrayCollection();
        }
        return $this->signatures;
    }

    public function addSignature(Signature $signature): self
    {
        if (!$this->getSignatures()->contains($signature)) {
            $this->getSignatures()->add($signature);
        }
        return $this;
    }

    public function removeSignature(Signature $signature): self
    {
        $this->getSignatures()->removeElement($signature);
        return $this;
    }

}
