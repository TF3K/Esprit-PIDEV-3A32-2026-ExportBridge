<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\SettingRepository;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ORM\Table(name: 'settings')]
class Setting
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

    #[ORM\OneToOne(targetEntity: Manager::class, inversedBy: 'setting')]
    #[ORM\JoinColumn(name: 'manager_id', referencedColumnName: 'id', unique: true)]
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $language = null;

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $theme = null;

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $email_notifications = null;

    public function isEmailNotifications(): ?bool
    {
        return $this->email_notifications;
    }

    public function setEmailNotifications(?bool $email_notifications): self
    {
        $this->email_notifications = $email_notifications;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $push_notifications = null;

    public function isPush_notifications(): ?bool
    {
        return $this->push_notifications;
    }

    public function setPush_notifications(?bool $push_notifications): self
    {
        $this->push_notifications = $push_notifications;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $certificate_expiry_alerts = null;

    public function isCertificate_expiry_alerts(): ?bool
    {
        return $this->certificate_expiry_alerts;
    }

    public function setCertificate_expiry_alerts(?bool $certificate_expiry_alerts): self
    {
        $this->certificate_expiry_alerts = $certificate_expiry_alerts;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $alert_days_before = null;

    public function getAlert_days_before(): ?int
    {
        return $this->alert_days_before;
    }

    public function setAlert_days_before(?int $alert_days_before): self
    {
        $this->alert_days_before = $alert_days_before;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $date_format = null;

    public function getDate_format(): ?string
    {
        return $this->date_format;
    }

    public function setDate_format(?string $date_format): self
    {
        $this->date_format = $date_format;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $currency = null;

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $timezone = null;

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

}
