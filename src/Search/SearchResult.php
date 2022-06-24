<?php

declare(strict_types=1);

namespace MeiliSearch\Search;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class SearchResult implements Countable, IteratorAggregate
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
    private int $estimatedTotalHits;

    private int $hitsCount;
    private int $offset;
    private int $limit;
    private int $processingTimeMs;

    private string $query;

    /**
     * @var array<string, mixed>
     */
    private array $facetDistribution;

    /**
     * @var array<string, mixed>
     */
    private array $raw;

    public function __construct(array $body)
    {
        $this->hits = $body['hits'] ?? [];
        $this->offset = $body['offset'];
        $this->limit = $body['limit'];
        $this->estimatedTotalHits = $body['estimatedTotalHits'];
        $this->hitsCount = \count($body['hits']);
        $this->processingTimeMs = $body['processingTimeMs'];
        $this->query = $body['query'];
        $this->facetDistribution = $body['facetDistribution'] ?? [];
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
     * @return SearchResult
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

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getHitsCount(): int
    {
        return $this->hitsCount;
    }

    public function count(): int
    {
        return $this->hitsCount;
    }

    public function getEstimatedTotalHits(): int
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

    /**
     * @return array<string, mixed>
     */
    public function getFacetDistribution(): array
    {
        return $this->facetDistribution;
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
        return [
            'hits' => $this->hits,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'estimatedTotalHits' => $this->estimatedTotalHits,
            'hitsCount' => $this->hitsCount,
            'processingTimeMs' => $this->processingTimeMs,
            'query' => $this->query,
            'facetDistribution' => $this->facetDistribution,
        ];
    }

    public function toJSON(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->hits);
    }
}
