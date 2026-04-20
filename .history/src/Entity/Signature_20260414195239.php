<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\SignatureRepository;

#[ORM\Entity(repositoryClass: SignatureRepository::class)]
#[ORM\Table(name: 'signatures')]
class Signature
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

    #[ORM\ManyToOne(targetEntity: Certificate::class, inversedBy: 'signatures')]
    #[ORM\JoinColumn(name: 'certificate_id', referencedColumnName: 'id')]
    private ?Certificate $certificate = null;

    public function getCertificate(): ?Certificate
    {
        return $this->certificate;
    }

    public function setCertificate(?Certificate $certificate): self
    {
        $this->certificate = $certificate;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $signatory_name = null;

    public function getSignatoryName(): ?string
    {
        return $this->signatory_name;
    }

    public function setSignatoryName(string $signatory_name): self
    {
        $this->signatory_name = $signatory_name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $signatory_title = null;

    public function getSignatoryTitle(): ?string
    {
        return $this->signatory_title;
    }

    public function setSignatoryTitle(?string $signatory_title): self
    {
        $this->signatory_title = $signatory_title;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $signed_date = null;

    public function getSignedDate(): ?\DateTimeInterface
    {
        return $this->signed_date;
    }

    public function setSignedDate(\DateTimeInterface $signed_date): self
    {
        $this->signed_date = $signed_date;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $digital_signature = null;

    public function getDigitalSignature(): ?string
    {
        return $this->digital_signature;
    }

    public function setDigitalSignature(?string $digital_signature): self
    {
        $this->digital_signature = $digital_signature;
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

}
