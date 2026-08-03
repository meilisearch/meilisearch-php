<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\DynamicSearchRule;
use Meilisearch\Contracts\DynamicSearchRulesQuery;
use Meilisearch\Contracts\DynamicSearchRulesResults;
use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\UpdateDynamicSearchRuleQuery;

/**
 * @phpstan-import-type RawDynamicSearchRule from DynamicSearchRule
 *
 * @phpstan-type RawDynamicSearchRules array{
 *     results: list<RawDynamicSearchRule>,
 *     offset: non-negative-int,
 *     limit: non-negative-int,
 *     total: non-negative-int
 * }
 * @phpstan-type RawDynamicSearchRuleTask array{
 *     taskUid: non-negative-int,
 *     indexUid?: non-empty-string|null,
 *     status: non-empty-string,
 *     type: non-empty-string,
 *     enqueuedAt: non-empty-string
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
        $query = null !== $options ? $options->toArray() : [];

        /** @var RawDynamicSearchRules $response */
        $response = $this->http->post(self::PATH, (object) $query);
        $response['results'] = array_map(static fn (array $data) => DynamicSearchRule::fromArray($data), $response['results']);

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
     *
     * @return RawDynamicSearchRuleTask
     */
    public function update(UpdateDynamicSearchRuleQuery $request): array
    {
        return $this->http->patch(self::PATH.'/'.$request->uid, $request->toArray());
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
     *
     * @return RawDynamicSearchRuleTask
     */
    public function delete(string $uid): array
    {
        $response = $this->http->delete(self::PATH.'/'.$uid);
        \assert(null !== $response);

        return $response;
    }

    /**
     * Delete all dynamic search rules.
     *
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * @since Meilisearch v1.50.0
     * @see https://www.meilisearch.com/docs/reference/api/search-rules/delete-a-search-rule
     *
     * @return RawDynamicSearchRuleTask
     */
    public function deleteAll(): array
    {
        $response = $this->http->delete(self::PATH);
        \assert(null !== $response);

        return $response;
    }
}
