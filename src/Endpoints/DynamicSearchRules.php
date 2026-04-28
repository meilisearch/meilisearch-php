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
 */
final class DynamicSearchRules extends Endpoint
{
    protected const PATH = '/dynamic-search-rules';

    public function all(?DynamicSearchRulesQuery $options = null): DynamicSearchRulesResults
    {
        $query = null !== $options ? $options->toArray() : [];

        $response = $this->http->post(self::PATH, (object) $query);
        $response['results'] = array_map(static fn (array $data) => DynamicSearchRule::fromArray($data), $response['results']);

        return new DynamicSearchRulesResults($response);
    }

    /**
     * @param non-empty-string $uid
     */
    public function get(string $uid): DynamicSearchRule
    {
        $response = $this->http->get(self::PATH.'/'.$uid);

        return DynamicSearchRule::fromArray($response);
    }

    public function update(UpdateDynamicSearchRuleQuery $request): DynamicSearchRule
    {
        $response = $this->http->patch(self::PATH.'/'.$request->uid, $request->toArray());

        return DynamicSearchRule::fromArray($response);
    }

    /**
     * @param non-empty-string $uid
     */
    public function delete(string $uid): ?array
    {
        return $this->http->delete(self::PATH.'/'.$uid);
    }
}
