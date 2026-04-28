<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;

/**
 * @phpstan-type RawDynamicSearchRule array{
 *     uid: non-empty-string,
 *     description?: string|null,
 *     priority?: non-negative-int|null,
 *     active?: bool,
 *     conditions?: list<array<string, mixed>>,
 *     actions: list<array<string, mixed>>
 * }
 * @phpstan-type RawDynamicSearchRules array{
 *     results: list<RawDynamicSearchRule>,
 *     offset: non-negative-int,
 *     limit: non-negative-int,
 *     total: non-negative-int
 * }
 * @phpstan-type DynamicSearchRulesQuery array{
 *     offset?: non-negative-int,
 *     limit?: non-negative-int,
 *     filter?: array<string, mixed>|null
 * }
 * @phpstan-type DynamicSearchRuleUpdatePayload array{
 *     description?: string|null,
 *     priority?: non-negative-int|null,
 *     active?: bool,
 *     conditions?: list<array<string, mixed>>,
 *     actions?: list<array<string, mixed>>
 * }
 */
class DynamicSearchRules extends Endpoint
{
    protected const PATH = '/dynamic-search-rules';

    /**
     * @param DynamicSearchRulesQuery $options
     *
     * @return RawDynamicSearchRules
     */
    public function all(array $options = []): array
    {
        return $this->http->post(self::PATH, (object) $options);
    }

    /**
     * @param non-empty-string $uid
     *
     * @return RawDynamicSearchRule
     */
    public function get(string $uid): array
    {
        return $this->http->get(self::PATH.'/'.$uid);
    }

    /**
     * @param non-empty-string               $uid
     * @param DynamicSearchRuleUpdatePayload $payload
     *
     * @return RawDynamicSearchRule
     */
    public function update(string $uid, array $payload): array
    {
        return $this->http->patch(self::PATH.'/'.$uid, $payload);
    }

    /**
     * @param non-empty-string $uid
     */
    public function delete(string $uid): ?array
    {
        return $this->http->delete(self::PATH.'/'.$uid);
    }
}
