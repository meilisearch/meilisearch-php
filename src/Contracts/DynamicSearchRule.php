<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type SearchRuleSelector array{
 *     indexUid?: non-empty-string|null,
 *     id: non-empty-string
 * }
 * @phpstan-type SearchRulePinAction array{
 *     type: 'pin',
 *     position: int
 * }
 * @phpstan-type SearchRuleAction array{
 *     selector: SearchRuleSelector,
 *     action: SearchRulePinAction
 * }
 * @phpstan-type QueryCondition array{
 *     isEmpty?: bool|null,
 *     words?: non-empty-string|null
 * }
 * @phpstan-type TimeCondition array{
 *     start?: non-empty-string|null,
 *     end?: non-empty-string|null
 * }
 * @phpstan-type SearchRuleConditions array{
 *     query?: QueryCondition|null,
 *     time?: TimeCondition|null
 * }
 * @phpstan-type RawDynamicSearchRule array{
 *     uid: non-empty-string,
 *     description?: string|null,
 *     precedence?: non-negative-int|null,
 *     active?: bool,
 *     conditions?: SearchRuleConditions|null,
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
    private readonly ?int $precedence;

    private readonly ?bool $active;

    /**
     * @var SearchRuleConditions|null
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
        $this->precedence = $raw['precedence'] ?? null;
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

    public function getPrecedence(): ?int
    {
        return $this->precedence;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @return SearchRuleConditions|null
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
