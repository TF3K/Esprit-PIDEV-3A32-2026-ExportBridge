<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Serializer\Annotation\Ignore;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $selector;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Ignore]
    private string $hashedToken;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeInterface $requestedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeInterface $expiresAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Manager $user = null;

    public function __construct(Manager $user, \DateTimeInterface $expiresAt, string $selector, #[\SensitiveParameter] string $hashedToken)
    {
        $this->user = $user;
        $this->requestedAt = Clock::get()->now();
        $this->expiresAt = $expiresAt;
        $this->selector = $selector;
        $this->setHashedToken($hashedToken);
    }

    public function getRequestedAt(): \DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= Clock::get()->now()->getTimestamp();
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getHashedToken(): string
    {
        return $this->hashedToken;
    }

    public function setHashedToken(#[\SensitiveParameter] string $hashedToken): self
    {
        $this->hashedToken = $hashedToken;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): Manager
    {
        return $this->user;
    }
}
