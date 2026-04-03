<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use App\Repository\ManagerRepository;

#[ORM\Entity(repositoryClass: ManagerRepository::class)]
#[ORM\Table(name: 'managers')]
class Manager implements UserInterface, PasswordAuthenticatedUserInterface
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
    private ?string $first_name = null;

    public function getFirst_name(): ?string
    {
        return $this->first_name;
    }

    public function setFirst_name(string $first_name): self
    {
        $this->first_name = $first_name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $last_name = null;

    public function getLast_name(): ?string
    {
        return $this->last_name;
    }

    public function setLast_name(string $last_name): self
    {
        $this->last_name = $last_name;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Companie::class, inversedBy: 'managers')]
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
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $password = null;

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $roles = null;

    public function getRoles(): ?string
    {
        return $this->roles;
    }

    public function setRoles(string $roles): self
    {
        $this->roles = $roles;
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

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $last_login = null;

    public function getLast_login(): ?\DateTimeInterface
    {
        return $this->last_login;
    }

    public function setLast_login(?\DateTimeInterface $last_login): self
    {
        $this->last_login = $last_login;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Companie::class, mappedBy: 'manager')]
    private Collection $companies;

    /**
     * @return Collection<int, Companie>
     */
    public function getCompanies(): Collection
    {
        if (!$this->companies instanceof Collection) {
            $this->companies = new ArrayCollection();
        }
        return $this->companies;
    }

    public function addCompanie(Companie $companie): self
    {
        if (!$this->getCompanies()->contains($companie)) {
            $this->getCompanies()->add($companie);
        }
        return $this;
    }

    public function removeCompanie(Companie $companie): self
    {
        $this->getCompanies()->removeElement($companie);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ContactHistory::class, mappedBy: 'manager')]
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

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'manager')]
    private Collection $notifications;

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        if (!$this->notifications instanceof Collection) {
            $this->notifications = new ArrayCollection();
        }
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->getNotifications()->contains($notification)) {
            $this->getNotifications()->add($notification);
        }
        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        $this->getNotifications()->removeElement($notification);
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Setting::class, mappedBy: 'manager')]
    private ?Setting $setting = null;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
        $this->contactHistorys = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getSetting(): ?Setting
    {
        return $this->setting;
    }

    public function setSetting(?Setting $setting): self
    {
        $this->setting = $setting;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

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

    public function getLastLogin(): ?\DateTime
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTime $last_login): static
    {
        $this->last_login = $last_login;

        return $this;
    }

    public function addCompany(Companie $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->setManager($this);
        }

        return $this;
    }

    public function removeCompany(Companie $company): static
    {
        if ($this->companies->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getManager() === $this) {
                $company->setManager(null);
            }
        }

        return $this;
    }

}
