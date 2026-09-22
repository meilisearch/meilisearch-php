<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-import-type SearchRuleActions from DynamicSearchRule
 * @phpstan-import-type SearchRuleConditions from DynamicSearchRule
 *
 * @since Meilisearch v1.54.0
 */
final class UpdateDynamicSearchRuleQuery
{
    private bool $hasDescription = false;
    private bool $hasPrecedence = false;
    private bool $hasActive = false;
    private bool $hasConditions = false;
    private bool $hasActions = false;

    private ?string $description = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $precedence = null;

    private ?bool $active = null;

    /**
     * @var SearchRuleConditions|null
     */
    private ?array $conditions = null;

    /**
     * @var SearchRuleActions|null
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
     * @param non-negative-int|null $precedence
     *
     * @return $this
     */
    public function setPrecedence(?int $precedence): self
    {
        $this->precedence = $precedence;
        $this->hasPrecedence = true;

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
     * @param SearchRuleConditions|null $conditions
     *
     * @return $this
     */
    public function setConditions(?array $conditions): self
    {
        $this->conditions = $conditions;
        $this->hasConditions = true;

        return $this;
    }

    /**
     * @param SearchRuleActions|null $actions pin and scale actions, or null to clear
     *
     * @return $this
     *
     * @since Meilisearch v1.54.0
     */
    public function setActions(?array $actions): self
    {
        $this->actions = $actions;
        $this->hasActions = true;

        return $this;
    }

    /**
     * @return array{
     *     description?: string|null,
     *     precedence?: non-negative-int|null,
     *     active?: bool,
     *     conditions?: SearchRuleConditions|null,
     *     actions?: SearchRuleActions|null
     * }
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->hasDescription) {
            $payload['description'] = $this->description;
        }

        if ($this->hasPrecedence) {
            $payload['precedence'] = $this->precedence;
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
