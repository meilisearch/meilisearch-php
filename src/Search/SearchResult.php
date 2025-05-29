<?php

declare(strict_types=1);

namespace Meilisearch\Search;

/**
 * @implements \IteratorAggregate<array<int, array<string, mixed>>>
 */
class SearchResult implements \Countable, \IteratorAggregate
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $hits;

    /**
     * `estimatedTotalHits` is the attributes returned by the Meilisearch server
     * and its value will not be modified by the methods in this class.
     * Please, use `hitsCount` if you want to know the real size of the `hits` array at any time.
     */
    private ?int $estimatedTotalHits = null;
    private int $hitsCount;
    private ?int $offset = null;
    private ?int $limit = null;
    private int $semanticHitCount;

    private ?int $hitsPerPage = null;
    private ?int $page = null;
    private ?int $totalPages = null;
    private ?int $totalHits = null;

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
        $this->hits = $body['hits'] ?? [];
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
     */
    public function applyOptions($options): self
    {
        if (\array_key_exists('transformHits', $options) && \is_callable($options['transformHits'])) {
            $this->transformHits($options['transformHits']);
        }
        if (\array_key_exists('transformFacetDistribution', $options) && \is_callable($options['transformFacetDistribution'])) {
            $this->transformFacetDistribution($options['transformFacetDistribution']);
        }

        return $this;
    }

    public function transformHits(callable $callback): self
    {
        $this->hits = $callback($this->hits);
        $this->hitsCount = \count($this->hits);

        return $this;
    }

    public function transformFacetDistribution(callable $callback): self
    {
        $this->facetDistribution = $callback($this->facetDistribution);

        return $this;
    }

    public function getHit(int $key, $default = null)
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

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

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

    public function count(): int
    {
        return $this->hitsCount;
    }

    public function getEstimatedTotalHits(): ?int
    {
        return $this->estimatedTotalHits;
    }

    public function getProcessingTimeMs(): int
    {
        return $this->processingTimeMs;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getHitsPerPage(): ?int
    {
        return $this->hitsPerPage;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getTotalPages(): ?int
    {
        return $this->totalPages;
    }

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

    public function toArray(): array
    {
        $arr = [
            'hits' => $this->hits,
            'hitsCount' => $this->hitsCount,
            'processingTimeMs' => $this->processingTimeMs,
            'query' => $this->query,
            'facetDistribution' => $this->facetDistribution,
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
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->hits);
    }
}
