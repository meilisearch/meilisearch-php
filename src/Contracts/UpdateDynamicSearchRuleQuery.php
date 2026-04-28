<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class UpdateDynamicSearchRuleQuery
{
    private bool $hasDescription = false;
    private bool $hasPriority = false;
    private bool $hasActive = false;
    private bool $hasConditions = false;
    private bool $hasActions = false;

    private ?string $description = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $priority = null;

    private ?bool $active = null;

    /**
     * @var list<array<string, mixed>>|null
     */
    private ?array $conditions = null;

    /**
     * @var list<array<string, mixed>>|null
     */
    private ?array $actions = null;

    /**
     * @param non-empty-string $uid
     */
    public function __construct(public readonly string $uid)
    {
    }

    /**
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        $this->hasDescription = true;

        return $this;
    }

    /**
     * @param non-negative-int|null $priority
     *
     * @return $this
     */
    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;
        $this->hasPriority = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        $this->hasActive = true;

        return $this;
    }

    /**
     * @param list<array<string, mixed>> $conditions
     *
     * @return $this
     */
    public function setConditions(array $conditions): self
    {
        $this->conditions = $conditions;
        $this->hasConditions = true;

        return $this;
    }

    /**
     * @param list<array<string, mixed>> $actions
     *
     * @return $this
     */
    public function setActions(array $actions): self
    {
        $this->actions = $actions;
        $this->hasActions = true;

        return $this;
    }

    /**
     * @return array{
     *     description?: string|null,
     *     priority?: non-negative-int|null,
     *     active?: bool,
     *     conditions?: list<array<string, mixed>>,
     *     actions?: list<array<string, mixed>>
     * }
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->hasDescription) {
            $payload['description'] = $this->description;
        }

        if ($this->hasPriority) {
            $payload['priority'] = $this->priority;
        }

        if ($this->hasActive) {
            $payload['active'] = $this->active;
        }

        if ($this->hasConditions) {
            $payload['conditions'] = $this->conditions;
        }

        if ($this->hasActions) {
            $payload['actions'] = $this->actions;
        }

        return $payload;
    }
}
