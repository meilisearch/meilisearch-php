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
     * Since Meilisearch v1.18.0, a query that sets `retrieveVectors` and runs its semantic part
     * (`semanticRatio` > 0), with either the given `vector` or one the embedder computed from `q`,
     * returns the vector it searched with. Without federation, the item of
     * `results` for that query includes `queryVector`. With federation, the top-level `queryVectors`
     * maps each such query's position to its vector; other queries have no entry.
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
     *     metadata?: list<SearchMetadata>,
     *     queryVectors?: array<int, list<float>>
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
         *     metadata?: list<SearchMetadata>,
         *     queryVectors?: array<int, list<float>>
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
