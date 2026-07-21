<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\DynamicSearchRule;
use Meilisearch\Contracts\DynamicSearchRulesQuery;
use Meilisearch\Contracts\DynamicSearchRulesResults;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\UpdateDynamicSearchRuleQuery;
use Meilisearch\Endpoints\DynamicSearchRules;

trait HandlesDynamicSearchRules
{
    protected DynamicSearchRules $dynamicSearchRules;

    /**
     * List dynamic search rules.
     *
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * @since Meilisearch v1.41.0
     * @see https://www.meilisearch.com/docs/reference/api/search-rules/list-search-rules
     */
    public function getDynamicSearchRules(?DynamicSearchRulesQuery $options = null): DynamicSearchRulesResults
    {
        return $this->dynamicSearchRules->all($options);
    }

    /**
     * Get a dynamic search rule.
     *
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * @param non-empty-string $uid Dynamic search rule UID
     *
     * @since Meilisearch v1.41.0
     * @see https://www.meilisearch.com/docs/reference/api/search-rules/get-a-search-rule
     */
    public function getDynamicSearchRule(string $uid): DynamicSearchRule
    {
        return $this->dynamicSearchRules->get($uid);
    }

    /**
     * Create or update a dynamic search rule.
     *
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * @since Meilisearch v1.41.0
     * @see https://www.meilisearch.com/docs/reference/api/search-rules/create-or-update-a-search-rule
     */
    public function updateDynamicSearchRule(UpdateDynamicSearchRuleQuery $request): Task
    {
        return $this->dynamicSearchRules->update($request);
    }

    /**
     * Delete a dynamic search rule.
     *
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * @param non-empty-string $uid Dynamic search rule UID
     *
     * @since Meilisearch v1.41.0
     * @see https://www.meilisearch.com/docs/reference/api/search-rules/delete-a-search-rule
     */
    public function deleteDynamicSearchRule(string $uid): Task
    {
        return $this->dynamicSearchRules->delete($uid);
    }

    /**
     * Delete all dynamic search rules.
     *
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * @since Meilisearch v1.50.0
     * @see https://www.meilisearch.com/docs/reference/api/search-rules/delete-a-search-rule
     */
    public function deleteAllDynamicSearchRules(): Task
    {
        return $this->dynamicSearchRules->deleteAll();
    }
}
