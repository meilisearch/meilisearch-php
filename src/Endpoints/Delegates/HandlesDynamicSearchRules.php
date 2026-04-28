<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\DynamicSearchRule;
use Meilisearch\Contracts\DynamicSearchRulesQuery;
use Meilisearch\Contracts\DynamicSearchRulesResults;
use Meilisearch\Contracts\UpdateDynamicSearchRuleQuery;
use Meilisearch\Endpoints\DynamicSearchRules;

trait HandlesDynamicSearchRules
{
    protected DynamicSearchRules $dynamicSearchRules;

    public function getDynamicSearchRules(?DynamicSearchRulesQuery $options = null): DynamicSearchRulesResults
    {
        return $this->dynamicSearchRules->all($options);
    }

    /**
     * @param non-empty-string $uid
     */
    public function getDynamicSearchRule(string $uid): DynamicSearchRule
    {
        return $this->dynamicSearchRules->get($uid);
    }

    public function updateDynamicSearchRule(UpdateDynamicSearchRuleQuery $request): DynamicSearchRule
    {
        return $this->dynamicSearchRules->update($request);
    }

    /**
     * @param non-empty-string $uid
     */
    public function deleteDynamicSearchRule(string $uid): ?array
    {
        return $this->dynamicSearchRules->delete($uid);
    }
}
