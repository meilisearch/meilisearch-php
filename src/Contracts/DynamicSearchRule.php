<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type SearchRuleSelector array{
 *     indexUid?: non-empty-string|null,
 *     id?: non-empty-string|null
 * }
 * @phpstan-type SearchRulePinAction array{
 *     type: 'pin',
 *     position: int
 * }
 * @phpstan-type SearchRuleAction array{
 *     selector: SearchRuleSelector,
 *     action: SearchRulePinAction
 * }
 * @phpstan-type SearchRuleQueryCondition array{
 *     scope: 'query',
 *     isEmpty?: bool|null,
 *     contains?: non-empty-string|null
 * }
 * @phpstan-type SearchRuleTimeCondition array{
 *     scope: 'time',
 *     start?: non-empty-string|null,
 *     end?: non-empty-string|null
 * }
 * @phpstan-type SearchRuleCondition SearchRuleQueryCondition|SearchRuleTimeCondition
 * @phpstan-type RawDynamicSearchRule array{
 *     uid: non-empty-string,
 *     description?: string|null,
 *     priority?: non-negative-int|null,
 *     active?: bool,
 *     conditions?: list<SearchRuleCondition>,
 *     actions: list<SearchRuleAction>
 * }
 */
final class DynamicSearchRule
{
    /**
     * @var non-empty-string
     */
    private readonly string $uid;

    /**
     * @var list<SearchRuleAction>
     */
    private readonly array $actions;

    private readonly ?string $description;

    /**
     * @var non-negative-int|null
     */
    private readonly ?int $priority;

    private readonly ?bool $active;

    /**
     * @var list<SearchRuleCondition>|null
     */
    private readonly ?array $conditions;

    /**
     * @param RawDynamicSearchRule $raw
     */
    public function __construct(
        private readonly array $raw,
    ) {
        $this->uid = $raw['uid'];
        $this->actions = $raw['actions'];
        $this->description = $raw['description'] ?? null;
        $this->priority = $raw['priority'] ?? null;
        $this->active = $raw['active'] ?? null;
        $this->conditions = $raw['conditions'] ?? null;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @return list<SearchRuleAction>
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
     * @return list<SearchRuleCondition>|null
     */
    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    /**
     * Return the original dynamic search rule.
     *
     * @return RawDynamicSearchRule
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * @return RawDynamicSearchRule
     */
    public function toArray(): array
    {
        return $this->raw;
    }

    /**
     * @param RawDynamicSearchRule $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
