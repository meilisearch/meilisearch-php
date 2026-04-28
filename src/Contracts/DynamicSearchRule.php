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
     * @param non-empty-string               $uid
     * @param list<SearchRuleAction>         $actions
     * @param non-negative-int|null          $priority
     * @param list<SearchRuleCondition>|null $conditions
     * @param array<string, mixed>           $raw
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
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * @return array<string, mixed>
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
