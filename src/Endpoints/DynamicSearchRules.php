<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\DynamicSearchRule;
use Meilisearch\Contracts\DynamicSearchRulesQuery;
use Meilisearch\Contracts\DynamicSearchRulesResults;
use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\UpdateDynamicSearchRuleQuery;

use function Meilisearch\partial;

/**
 * @phpstan-import-type RawDynamicSearchRule from DynamicSearchRule
 *
 * @phpstan-type RawDynamicSearchRules array{
 *     results: list<RawDynamicSearchRule>,
 *     offset: non-negative-int,
 *     limit: non-negative-int,
 *     total: non-negative-int
 * }
 */
final class DynamicSearchRules extends Endpoint
{
    protected const PATH = '/dynamic-search-rules';

    /**
     * List dynamic search rules.
     *
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * @since Meilisearch v1.41.0
     * @see https://www.meilisearch.com/docs/reference/api/search-rules/list-search-rules
     */
    public function all(?DynamicSearchRulesQuery $options = null): DynamicSearchRulesResults
    {
        $query = $options?->toArray() ?? [];

        $rawResponse = $this->http->post(self::PATH, (object) $query);
        /** @var RawDynamicSearchRules $response */
        $response = $rawResponse;
        $results = [];
        foreach ($response['results'] as $data) {
            $results[] = DynamicSearchRule::fromArray($data);
        }
        $response['results'] = $results;

        return new DynamicSearchRulesResults($response);
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
    public function get(string $uid): DynamicSearchRule
    {
        $response = $this->http->get(self::PATH.'/'.$uid);

        return DynamicSearchRule::fromArray($response);
    }

    /**
     * Create or update a dynamic search rule.
     *
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * @since Meilisearch v1.41.0
     * @see https://www.meilisearch.com/docs/reference/api/search-rules/create-or-update-a-search-rule
     */
    public function update(UpdateDynamicSearchRuleQuery $request): Task
    {
        return Task::fromArray(
            $this->http->patch(self::PATH.'/'.$request->uid, $request->toArray()),
            partial(Tasks::waitTask(...), $this->http)
        );
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
    public function delete(string $uid): Task
    {
        $response = $this->http->delete(self::PATH.'/'.$uid);
        \assert(null !== $response);

        return Task::fromArray($response, partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * Delete all dynamic search rules.
     *
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * @since Meilisearch v1.50.0
     * @see https://www.meilisearch.com/docs/reference/api/search-rules/delete-a-search-rule
     */
    public function deleteAll(): Task
    {
        $response = $this->http->delete(self::PATH);
        \assert(null !== $response);

        return Task::fromArray($response, partial(Tasks::waitTask(...), $this->http));
    }
}
