<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\PartnershipRepository;

#[ORM\Entity(repositoryClass: PartnershipRepository::class)]
#[ORM\Table(name: 'partnerships')]
class Partnership
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

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'partnerships')]
    #[ORM\JoinColumn(name: 'source_company_id', referencedColumnName: 'id', nullable: true)]
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

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'target_company_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Company $targetCompany = null;

    public function getTargetCompany(): ?Company
    {
        return $this->targetCompany;
    }

    public function setTargetCompany(?Company $targetCompany): self
    {
        $this->targetCompany = $targetCompany;
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
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $established_date = null;

    public function getEstablishedDate(): ?\DateTimeInterface
    {
        return $this->established_date;
    }

    public function setEstablishedDate(?\DateTimeInterface $established_date): self
    {
        $this->established_date = $established_date;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $terminated_date = null;

    public function getTerminatedDate(): ?\DateTimeInterface
    {
        return $this->terminated_date;
    }

    public function setTerminatedDate(?\DateTimeInterface $terminated_date): self
    {
        $this->terminated_date = $terminated_date;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }


    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $document_url = null;

    public function getDocumentUrl(): ?string
    {
        return $this->document_url;
    }

    public function setDocumentUrl(?string $document_url): self
    {
        $this->document_url = $document_url;
        return $this;
    }


    #[ORM\Column(name: 'location_name', type: 'string', length: 255, nullable: true)]
    private ?string $locationName = null;

    public function getLocationName(): ?string
    {
        return $this->locationName;
    }

    public function setLocationName(?string $locationName): self
    {
        $this->locationName = $locationName;
        return $this;
    }

    #[ORM\Column(name: 'location_latitude', type: 'float', nullable: true)]
    private ?float $locationLatitude = null;

    public function getLocationLatitude(): ?float
    {
        return $this->locationLatitude;
    }

    public function setLocationLatitude(?float $locationLatitude): self
    {
        $this->locationLatitude = $locationLatitude;
        return $this;
    }

    #[ORM\Column(name: 'location_longitude', type: 'float', nullable: true)]
    private ?float $locationLongitude = null;

    public function getLocationLongitude(): ?float
    {
        return $this->locationLongitude;
    }

    public function setLocationLongitude(?float $locationLongitude): self
    {
        $this->locationLongitude = $locationLongitude;
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

    #[ORM\OneToMany(targetEntity: Collaboration::class, mappedBy: 'partnership')]
    private Collection $collaborations;

    #[ORM\OneToMany(mappedBy: 'partnership', targetEntity: Certificate::class)]
    private Collection $certificates;

    public function __construct()
    {
        $this->collaborations = new ArrayCollection();
        $this->certificates = new ArrayCollection();
    }

    /**
     * @return Collection<int, Collaboration>
     */
    public function getCollaborations(): Collection
    {
        if (!$this->collaborations instanceof Collection) {
            $this->collaborations = new ArrayCollection();
        }
        return $this->collaborations;
    }

    public function addCollaboration(Collaboration $collaboration): self
    {
        if (!$this->getCollaborations()->contains($collaboration)) {
            $this->getCollaborations()->add($collaboration);
        }
        return $this;
    }

    public function removeCollaboration(Collaboration $collaboration): self
    {
        $this->getCollaborations()->removeElement($collaboration);
        return $this;
    }

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
            $certificate->setPartnership($this);
        }

        return $this;
    }

    public function removeCertificate(Certificate $certificate): self
    {
        if ($this->certificates->removeElement($certificate)) {
            if ($certificate->getPartnership() === $this) {
                $certificate->setPartnership(null);
            }
        }

        return $this;
    }

}
