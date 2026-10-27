<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\MultiSearchFederation;
use Meilisearch\Contracts\MultiSearchQuery;

/**
 * @phpstan-import-type SearchMetadata from \Meilisearch\Search\SearchResult
 */
trait HandlesMultiSearch
{
    protected Http $http;

    /**
     * Pass `$includeMetadata` to send `Meili-Include-Metadata: true`. Available since Meilisearch v1.55.0.
     * Without federation, each item of `results` then includes `metadata`.
     * With federation, `metadata` is a top-level list with one entry per query.
     *
     * @param list<MultiSearchQuery> $queries
     *
     * @return array{
     *     results?: list<array<string, mixed>>,
     *     hits?: list<array<string, mixed>>,
     *     processingTimeMs?: non-negative-int,
     *     limit?: non-negative-int,
     *     offset?: non-negative-int,
     *     estimatedTotalHits?: non-negative-int,
     *     facetDistribution?: array<string, mixed>,
     *     facetStats?: array<string, mixed>,
     *     metadata?: list<SearchMetadata>
     * }
     *
     * @see https://www.meilisearch.com/docs/reference/api/multi-search/perform-a-multi-search
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
         *     facetStats?: array<string, mixed>,
         *     metadata?: list<SearchMetadata>
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
