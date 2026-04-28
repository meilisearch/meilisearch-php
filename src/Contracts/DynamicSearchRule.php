<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class DynamicSearchRule
{
    /**
     * @param non-empty-string                $uid
     * @param list<array<string, mixed>>      $actions
     * @param non-negative-int|null           $priority
     * @param list<array<string, mixed>>|null $conditions
     * @param array<string, mixed>            $raw
     */
    public function __construct(
        private readonly string $uid,
        private readonly array $actions,
        private readonly ?string $description = null,
        private readonly ?int $priority = null,
        private readonly ?bool $active = null,
        private readonly ?array $conditions = null,
        private readonly array $raw = [],
    ) {
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    /**
     * Return the original dynamic search rule.
     *
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * @return array{
     *     uid: non-empty-string,
     *     description?: string|null,
     *     priority?: non-negative-int|null,
     *     active?: bool,
     *     conditions?: list<array<string, mixed>>,
     *     actions: list<array<string, mixed>>
     * }
     */
    public function toArray(): array
    {
        return $this->raw;
    }

    /**
     * @param array{
     *     uid: non-empty-string,
     *     description?: string|null,
     *     priority?: non-negative-int|null,
     *     active?: bool,
     *     conditions?: list<array<string, mixed>>,
     *     actions: list<array<string, mixed>>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            uid: $data['uid'],
            actions: $data['actions'],
            description: $data['description'] ?? null,
            priority: $data['priority'] ?? null,
            active: $data['active'] ?? null,
            conditions: $data['conditions'] ?? null,
            raw: $data,
        );
    }
}
