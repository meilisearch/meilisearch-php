<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\MultiSearchFederation;
use Meilisearch\Contracts\MultiSearchQuery;

trait HandlesMultiSearch
{
    protected Http $http;

    /**
     * @param list<MultiSearchQuery> $queries
     * @param bool                   $includeMetadata sends `Meili-Include-Metadata: true`, so each result has a `metadata` key
     *
     * @return array{
     *     results?: list<array<string, mixed>>,
     *     hits?: list<array<string, mixed>>,
     *     processingTimeMs?: non-negative-int,
     *     limit?: non-negative-int,
     *     offset?: non-negative-int,
     *     estimatedTotalHits?: non-negative-int,
     *     facetDistribution?: array<string, mixed>,
     *     facetStats?: array<string, mixed>
     * }
     */
    public function multiSearch(array $queries = [], ?MultiSearchFederation $federation = null, bool $includeMetadata = false): array
    {
        $body = [];

        foreach ($queries as $query) {
            $body[] = $query->toArray();
        }

        $payload = ['queries' => $body];
        if (null !== $federation) {
            $payload['federation'] = (object) $federation->toArray();
        }

        /** @var array{
         *     results?: list<array<string, mixed>>,
         *     hits?: list<array<string, mixed>>,
         *     processingTimeMs?: non-negative-int,
         *     limit?: non-negative-int,
         *     offset?: non-negative-int,
         *     estimatedTotalHits?: non-negative-int,
         *     facetDistribution?: array<string, mixed>,
         *     facetStats?: array<string, mixed>
         * } $result
         */
        $result = $this->http->post(
            '/multi-search',
            $payload,
            headers: $includeMetadata ? ['Meili-Include-Metadata' => 'true'] : [],
        );

        return $result;
    }
}
