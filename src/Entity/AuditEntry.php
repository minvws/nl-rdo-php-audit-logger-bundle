<?php

declare(strict_types=1);

namespace MinVWS\AuditLoggerBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class AuditEntry
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * @var array<array-key,mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $request;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $eventCode;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $actionCode;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $failed;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return array<array-key,mixed>
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * @param array<array-key,mixed> $request
     */
    public function setRequest(array $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function getEventCode(): ?string
    {
        return $this->eventCode;
    }

    public function setEventCode(string $eventCode): static
    {
        $this->eventCode = $eventCode;

        return $this;
    }

    public function getActionCode(): ?string
    {
        return $this->actionCode;
    }

    public function setActionCode(string $actionCode): static
    {
        $this->actionCode = $actionCode;

        return $this;
    }

    public function isFailed(): ?bool
    {
        return $this->failed;
    }

    public function setFailed(bool $failed): static
    {
        $this->failed = $failed;

        return $this;
    }
}
