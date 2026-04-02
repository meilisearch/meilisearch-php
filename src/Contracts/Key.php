<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class Key
{
    /**
     * @param non-empty-string       $uid
     * @param non-empty-string       $key
     * @param list<KeyAction>        $actions
     * @param list<non-empty-string> $indexes
     * @param non-empty-string|null  $name
     * @param non-empty-string|null  $description
     */
    public function __construct(
        private readonly string $uid,
        private readonly string $key,
        private readonly array $actions,
        private readonly array $indexes,
        private readonly ?string $name,
        private readonly ?string $description,
        private readonly ?\DateTimeInterface $expiresAt,
        private readonly ?\DateTimeInterface $createdAt,
        private readonly ?\DateTimeInterface $updatedAt,
    ) {
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return list<KeyAction>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @return list<non-empty-string>
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param array{
     *     uid: non-empty-string,
     *     key: non-empty-string,
     *     actions: list<non-empty-string>,
     *     indexes: list<non-empty-string>,
     *     name?: non-empty-string,
     *     description?: non-empty-string,
     *     expiresAt?: non-empty-string,
     *     createdAt: non-empty-string,
     *     updatedAt: non-empty-string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['uid'],
            $data['key'],
            array_map(static fn (string $action) => KeyAction::from($action), $data['actions']),
            $data['indexes'],
            $data['name'] ?? null,
            $data['description'] ?? null,
            isset($data['expiresAt']) ? new \DateTimeImmutable($data['expiresAt']) : null,
            new \DateTimeImmutable($data['createdAt']),
            new \DateTimeImmutable($data['updatedAt']),
        );
    }
}
