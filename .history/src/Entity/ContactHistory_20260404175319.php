<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ContactHistoryRepository;

#[ORM\Entity(repositoryClass: ContactHistoryRepository::class)]
#[ORM\Table(name: 'contact_history')]
class ContactHistory
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

    #[ORM\ManyToOne(targetEntity: Companie::class, inversedBy: 'contactHistorys')]
    #[ORM\JoinColumn(name: 'source_company_id', referencedColumnName: 'id')]
    private ?Companie $companie = null;

    public function getCompany(): ?Company
    {
        return $this->companie;
    }

    public function setCompany(?Company $companie): self
    {
        $this->companie = $companie;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $contact_date = null;

    public function getContactDate(): ?\DateTimeInterface
    {
        return $this->contact_date;
    }

    public function setContactDate(\DateTimeInterface $contact_date): self
    {
        $this->contact_date = $contact_date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $contact_type = null;

    public function getContact_type(): ?string
    {
        return $this->contact_type;
    }

    public function setContact_type(string $contact_type): self
    {
        $this->contact_type = $contact_type;
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

    #[ORM\ManyToOne(targetEntity: Manager::class, inversedBy: 'contactHistorys')]
    #[ORM\JoinColumn(name: 'contacted_by_manager_id', referencedColumnName: 'id')]
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

    public function getContactDate(): ?\DateTime
    {
        return $this->contact_date;
    }

    public function setContactDate(\DateTime $contact_date): static
    {
        $this->contact_date = $contact_date;

        return $this;
    }

    public function getContactType(): ?string
    {
        return $this->contact_type;
    }

    public function setContactType(string $contact_type): static
    {
        $this->contact_type = $contact_type;

        return $this;
    }

}
