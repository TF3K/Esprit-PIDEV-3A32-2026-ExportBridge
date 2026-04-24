<?php

namespace App\Entity;

use App\Repository\ManagerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: ManagerRepository::class)]
#[ORM\Table(name: 'managers')]
class Manager implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $first_name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $last_name = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $last_login = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'managers')]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: true)]
    private ?Company $company = null;

    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'manager')]
    private Collection $companies;

    #[ORM\OneToMany(targetEntity: ContactHistory::class, mappedBy: 'manager')]
    private Collection $contactHistory;

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'manager')]
    private Collection $notifications;

    #[ORM\OneToOne(targetEntity: Setting::class, mappedBy: 'manager')]
    private ?Setting $setting = null;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
        $this->contactHistory = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    /* ===================== ID ===================== */

    public function getId(): ?int
    {
        return $this->id;
    }

    /* ===================== FIRST NAME ===================== */

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;
        return $this;
    }

    /* ===================== LAST NAME ===================== */

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;
        return $this;
    }

    /* ===================== EMAIL ===================== */

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower($email);
        return $this;
    }

    /* ===================== PASSWORD ===================== */

    public function getPassword(): string
    {
        return $this->password ?? '';
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /* ===================== ROLES ===================== */

    public function getRoles(): array
    {
        $roles = $this->roles;

        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /* ===================== USER IDENTIFIER ===================== */

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function eraseCredentials(): void
    {
        // clear temporary sensitive data if needed
    }

    /* ===================== CREATED AT ===================== */

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    /* ===================== LAST LOGIN ===================== */

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTimeInterface $last_login): self
    {
        $this->last_login = $last_login;
        return $this;
    }

    /* ===================== COMPANY ===================== */

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /* ===================== COMPANIES ===================== */

    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        $this->companies->removeElement($company);
        return $this;
    }

    /* ===================== CONTACT HISTORY ===================== */

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

    /* ===================== NOTIFICATIONS ===================== */

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        $this->notifications->removeElement($notification);
        return $this;
    }

    /* ===================== SETTING ===================== */

    public function getSetting(): ?Setting
    {
        return $this->setting;
    }

    public function setSetting(?Setting $setting): self
    {
        $this->setting = $setting;
        return $this;
    }
}