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

    #[ORM\ManyToOne(targetEntity: Companie::class, inversedBy: 'certificates')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id')]
    private ?Companie $companie = null;

    public function getCompanie(): ?Companie
    {
        return $this->companie;
    }

    public function setCompanie(?Companie $companie): self
    {
        $this->companie = $companie;
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

    public function getCertificate_number(): ?string
    {
        return $this->certificate_number;
    }

    public function setCertificate_number(string $certificate_number): self
    {
        $this->certificate_number = $certificate_number;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $issue_date = null;

    public function getIssue_date(): ?\DateTimeInterface
    {
        return $this->issue_date;
    }

    public function setIssue_date(?\DateTimeInterface $issue_date): self
    {
        $this->issue_date = $issue_date;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $expiry_date = null;

    public function getExpiry_date(): ?\DateTimeInterface
    {
        return $this->expiry_date;
    }

    public function setExpiry_date(?\DateTimeInterface $expiry_date): self
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

    public function getCountry_of_origin(): ?string
    {
        return $this->country_of_origin;
    }

    public function setCountry_of_origin(?string $country_of_origin): self
    {
        $this->country_of_origin = $country_of_origin;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $issuing_authority = null;

    public function getIssuing_authority(): ?string
    {
        return $this->issuing_authority;
    }

    public function setIssuing_authority(?string $issuing_authority): self
    {
        $this->issuing_authority = $issuing_authority;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $document_file = null;

    public function getDocument_file(): ?string
    {
        return $this->document_file;
    }

    public function setDocument_file(?string $document_file): self
    {
        $this->document_file = $document_file;
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

    public function getCertificateNumber(): ?string
    {
        return $this->certificate_number;
    }

    public function setCertificateNumber(string $certificate_number): static
    {
        $this->certificate_number = $certificate_number;

        return $this;
    }

    public function getIssueDate(): ?\DateTime
    {
        return $this->issue_date;
    }

    public function setIssueDate(?\DateTime $issue_date): static
    {
        $this->issue_date = $issue_date;

        return $this;
    }

    public function getExpiryDate(): ?\DateTime
    {
        return $this->expiry_date;
    }

    public function setExpiryDate(?\DateTime $expiry_date): static
    {
        $this->expiry_date = $expiry_date;

        return $this;
    }

    public function getCountryOfOrigin(): ?string
    {
        return $this->country_of_origin;
    }

    public function setCountryOfOrigin(?string $country_of_origin): static
    {
        $this->country_of_origin = $country_of_origin;

        return $this;
    }

    public function getIssuingAuthority(): ?string
    {
        return $this->issuing_authority;
    }

    public function setIssuingAuthority(?string $issuing_authority): static
    {
        $this->issuing_authority = $issuing_authority;

        return $this;
    }

    public function getDocumentFile(): ?string
    {
        return $this->document_file;
    }

    public function setDocumentFile(?string $document_file): static
    {
        $this->document_file = $document_file;

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
