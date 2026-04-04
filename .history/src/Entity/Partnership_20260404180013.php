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

    #[ORM\OneToOne(targetEntity: Companie::class, inversedBy: 'partnership')]
    #[ORM\JoinColumn(name: 'source_company_id', referencedColumnName: 'id', unique: true)]
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

    public function getEstablished_date(): ?\DateTimeInterface
    {
        return $this->established_date;
    }

    public function setEstablished_date(?\DateTimeInterface $established_date): self
    {
        $this->established_date = $established_date;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $terminated_date = null;

    public function getTerminated_date(): ?\DateTimeInterface
    {
        return $this->terminated_date;
    }

    public function setTerminated_date(?\DateTimeInterface $terminated_date): self
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

    #[ORM\OneToMany(targetEntity: Collaboration::class, mappedBy: 'partnership')]
    private Collection $collaborations;

    public function __construct()
    {
        $this->collaborations = new ArrayCollection();
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

    public function getEstablishedDate(): ?\DateTime
    {
        return $this->established_date;
    }

    public function setEstablishedDate(?\DateTime $established_date): static
    {
        $this->established_date = $established_date;

        return $this;
    }

    public function getTerminatedDate(): ?\DateTime
    {
        return $this->terminated_date;
    }

    public function setTerminatedDate(?\DateTime $terminated_date): static
    {
        $this->terminated_date = $terminated_date;

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
