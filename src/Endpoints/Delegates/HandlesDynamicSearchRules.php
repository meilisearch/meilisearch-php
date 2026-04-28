<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Endpoints\DynamicSearchRules;

/**
 * @phpstan-import-type DynamicSearchRuleUpdatePayload from DynamicSearchRules
 * @phpstan-import-type DynamicSearchRulesQuery from DynamicSearchRules
 * @phpstan-import-type RawDynamicSearchRule from DynamicSearchRules
 * @phpstan-import-type RawDynamicSearchRules from DynamicSearchRules
 */
trait HandlesDynamicSearchRules
{
    protected DynamicSearchRules $dynamicSearchRules;

    /**
     * @param DynamicSearchRulesQuery $options
     *
     * @return RawDynamicSearchRules
     */
    public function getDynamicSearchRules(array $options = []): array
    {
        return $this->dynamicSearchRules->all($options);
    }

    /**
     * @param non-empty-string $uid
     *
     * @return RawDynamicSearchRule
     */
    public function getDynamicSearchRule(string $uid): array
    {
        return $this->dynamicSearchRules->get($uid);
    }

    /**
     * @param non-empty-string               $uid
     * @param DynamicSearchRuleUpdatePayload $payload
     *
     * @return RawDynamicSearchRule
     */
    public function updateDynamicSearchRule(string $uid, array $payload): array
    {
        return $this->dynamicSearchRules->update($uid, $payload);
    }

    /**
     * @param non-empty-string $uid
     */
    public function deleteDynamicSearchRule(string $uid): ?array
    {
        return $this->dynamicSearchRules->delete($uid);
    }
}
