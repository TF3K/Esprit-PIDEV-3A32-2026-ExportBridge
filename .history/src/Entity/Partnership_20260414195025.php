<?php

namespace App\Entity;

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

    #[ORM\OneToOne(targetEntity: Company::class, inversedBy: 'partnership')]
    #[ORM\JoinColumn(name: 'source_company_id', referencedColumnName: 'id', unique: true)]
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

}
