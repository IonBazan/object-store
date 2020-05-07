<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Infrastructure\Persistence\Repository\ObjectEntryRepository")
 * @ORM\Table(
 *     name="object_entry",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="key_date_idx", columns={"key", "created_at"})
 *     }
 * )
 */
class ObjectEntry
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    protected UuidInterface $id;

    /**
     * @ORM\Column(type="string", name="`key`", options={"collation": "utf8mb4_bin", "charset": "utf8mb4"})
     */
    protected string $key;

    /**
     * @ORM\Column(type="datetime", columnDefinition="timestamp not null")
     */
    protected DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="json", name="`value`", nullable=true)
     */
    protected $value;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }
}
