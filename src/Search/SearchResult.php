<?php

declare(strict_types=1);

namespace Meilisearch\Search;

/**
 * @implements \IteratorAggregate<int, array<string, mixed>>
 */
final class SearchResult implements \Countable, \IteratorAggregate
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $hits;

    /**
     * `estimatedTotalHits` is the attributes returned by the Meilisearch server
     * and its value will not be modified by the methods in this class.
     * Please, use `hitsCount` if you want to know the real size of the `hits` array at any time.
     *
     * @var non-negative-int|null
     */
    private ?int $estimatedTotalHits = null;

    /**
     * @var non-negative-int
     */
    private int $hitsCount;

    /**
     * @var non-negative-int|null
     */
    private ?int $offset = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $limit = null;

    /**
     * @var non-negative-int
     */
    private int $semanticHitCount;

    /**
     * @var non-negative-int|null
     */
    private ?int $hitsPerPage = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $page = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $totalPages = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $totalHits = null;

    /**
     * @var non-negative-int
     */
    private int $processingTimeMs;

    private bool $numberedPagination;

    private string $query;

    /**
     * @var array<string, mixed>
     */
    private array $facetDistribution;

    /**
     * @var array<string, mixed>
     */
    private array $facetStats;

    /**
     * @var array<string, mixed>
     */
    private array $raw;

    /**
     * @param array{
     *     hits: array<int, array<string, mixed>>,
     *     processingTimeMs: non-negative-int,
     *     query: string,
     *     facetDistribution?: array<string, mixed>,
     *     facetStats?: array<string, mixed>,
     *     offset?: non-negative-int,
     *     limit?: non-negative-int,
     *     semanticHitCount?: non-negative-int,
     *     page?: non-negative-int,
     *     totalPages?: non-negative-int,
     *     totalHits?: non-negative-int,
     *     estimatedTotalHits?: non-negative-int,
     *     hitsPerPage?: non-negative-int,
     * } $body
     */
    public function __construct(array $body)
    {
        if (isset($body['estimatedTotalHits'])) {
            $this->numberedPagination = false;

            $this->offset = $body['offset'];
            $this->limit = $body['limit'];
            $this->estimatedTotalHits = $body['estimatedTotalHits'];
        } else {
            $this->numberedPagination = true;

            $this->hitsPerPage = $body['hitsPerPage'];
            $this->page = $body['page'];
            $this->totalPages = $body['totalPages'];
            $this->totalHits = $body['totalHits'];
        }

        $this->semanticHitCount = $body['semanticHitCount'] ?? 0;
        $this->hits = $body['hits'];
        $this->hitsCount = \count($body['hits']);
        $this->processingTimeMs = $body['processingTimeMs'];
        $this->query = $body['query'];
        $this->facetDistribution = $body['facetDistribution'] ?? [];
        $this->facetStats = $body['facetStats'] ?? [];
        $this->raw = $body;
    }

    /**
     * Return a new {@see SearchResult} instance.
     *
     * The $options parameter is an array, and the following keys are accepted:
     * - transformFacetDistribution (callable)
     * - transformHits (callable)
     *
     * The method does NOT trigger a new search.
     *
     * @param array{
     *     transformHits?: callable(array<int, array<string, mixed>>): array<int, array<string, mixed>>,
     *     transformFacetDistribution?: callable(array<string, mixed>): array<string, mixed>
     * } $options
     */
    public function applyOptions(array $options): self
    {
        if (\array_key_exists('transformHits', $options)) {
            $this->transformHits($options['transformHits']);
        }
        if (\array_key_exists('transformFacetDistribution', $options)) {
            $this->transformFacetDistribution($options['transformFacetDistribution']);
        }

        return $this;
    }

    /**
     * @param callable(array<int, array<string, mixed>>): array<int, array<string, mixed>> $callback
     */
    public function transformHits(callable $callback): self
    {
        $this->hits = $callback($this->hits);
        $this->hitsCount = \count($this->hits);

        return $this;
    }

    /**
     * @param callable(array<string, mixed>): array<string, mixed> $callback
     */
    public function transformFacetDistribution(callable $callback): self
    {
        $this->facetDistribution = $callback($this->facetDistribution);

        return $this;
    }

    /**
     * @template TDefault
     *
     * @param TDefault $default
     *
     * @return array<string, mixed>|TDefault
     */
    public function getHit(int $key, mixed $default = null): mixed
    {
        return $this->hits[$key] ?? $default;
    }

    /**
     * @return array<int, array>
     */
    public function getHits(): array
    {
        return $this->hits;
    }

    /**
     * @return non-negative-int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @return non-negative-int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @return non-negative-int
     */
    public function getHitsCount(): int
    {
        return $this->hitsCount;
    }

    /**
     * @return non-negative-int
     */
    public function getSemanticHitCount(): int
    {
        return $this->semanticHitCount;
    }

    /**
     * @return non-negative-int
     */
    public function count(): int
    {
        return $this->hitsCount;
    }

    /**
     * @return non-negative-int|null
     */
    public function getEstimatedTotalHits(): ?int
    {
        return $this->estimatedTotalHits;
    }

    /**
     * @return non-negative-int
     */
    public function getProcessingTimeMs(): int
    {
        return $this->processingTimeMs;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return non-negative-int|null
     */
    public function getHitsPerPage(): ?int
    {
        return $this->hitsPerPage;
    }

    /**
     * @return non-negative-int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @return non-negative-int|null
     */
    public function getTotalPages(): ?int
    {
        return $this->totalPages;
    }

    /**
     * @return non-negative-int|null
     */
    public function getTotalHits(): ?int
    {
        return $this->totalHits;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFacetDistribution(): array
    {
        return $this->facetDistribution;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFacetStats(): array
    {
        return $this->facetStats;
    }

    /**
     * Return the original search result.
     *
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * @return array{
     *     hits: array<int, array<string, mixed>>,
     *     hitsCount: non-negative-int,
     *     processingTimeMs: non-negative-int,
     *     query: string,
     *     facetDistribution: array<string, mixed>,
     *     facetStats: array<string, mixed>,
     *     offset?: non-negative-int,
     *     limit?: non-negative-int,
     *     estimatedTotalHits?: non-negative-int,
     *     hitsPerPage?: non-negative-int,
     *     page?: non-negative-int,
     *     totalPages?: non-negative-int,
     *     totalHits?: non-negative-int,
     * }
     */
    public function toArray(): array
    {
        $arr = [
            'hits' => $this->hits,
            'hitsCount' => $this->hitsCount,
            'processingTimeMs' => $this->processingTimeMs,
            'query' => $this->query,
            'facetDistribution' => $this->facetDistribution,
            'facetStats' => $this->facetStats,
        ];

        if (!$this->numberedPagination) {
            $arr = array_merge($arr, [
                'offset' => $this->offset,
                'limit' => $this->limit,
                'estimatedTotalHits' => $this->estimatedTotalHits,
            ]);
        } else {
            $arr = array_merge($arr, [
                'hitsPerPage' => $this->hitsPerPage,
                'page' => $this->page,
                'totalPages' => $this->totalPages,
                'totalHits' => $this->totalHits,
            ]);
        }

        return $arr;
    }

    public function toJSON(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->hits);
    }
}
