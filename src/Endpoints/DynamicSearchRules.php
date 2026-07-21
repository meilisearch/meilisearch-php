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
     * @param non-empty-string $uid
     */
    public function get(string $uid): DynamicSearchRule
    {
        $response = $this->http->get(self::PATH.'/'.$uid);

        return DynamicSearchRule::fromArray($response);
    }

    public function update(UpdateDynamicSearchRuleQuery $request): Task
    {
        return Task::fromArray(
            $this->http->patch(self::PATH.'/'.$request->uid, $request->toArray()),
            partial(Tasks::waitTask(...), $this->http)
        );
    }

    /**
     * @param non-empty-string $uid
     */
    public function delete(string $uid): Task
    {
        $response = $this->http->delete(self::PATH.'/'.$uid);
        \assert(null !== $response);

        return Task::fromArray($response, partial(Tasks::waitTask(...), $this->http));
    }

    public function deleteAll(): Task
    {
        $response = $this->http->delete(self::PATH);
        \assert(null !== $response);

        return Task::fromArray($response, partial(Tasks::waitTask(...), $this->http));
    }
}
