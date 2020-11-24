<?php

declare(strict_types=1);

namespace MeiliSearch\Search;

use function array_filter;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class SearchResult implements Countable, IteratorAggregate
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private $hits;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $nbHits;

    /**
     * @var bool
     */
    private $exhaustiveNbHits;

    /**
     * @var int
     */
    private $processingTimeMs;

    /**
     * @var string
     */
    private $query;

    /**
     * @var bool|null
     */
    private $exhaustiveFacetsCount;

    /**
     * @var array<string, mixed>
     */
    private $facetsDistribution;

    /**
     * @var array<string, mixed>
     */
    private $raw;

    public function __construct(array $body)
    {
        $this->hits = $body['hits'] ?? [];
        $this->offset = $body['offset'];
        $this->limit = $body['limit'];
        $this->nbHits = $body['nbHits'];
        $this->exhaustiveNbHits = $body['exhaustiveNbHits'] ?? false;
        $this->processingTimeMs = $body['processingTimeMs'];
        $this->query = $body['query'];
        $this->exhaustiveFacetsCount = $body['exhaustiveFacetsCount'] ?? null;
        $this->facetsDistribution = $body['facetsDistribution'] ?? [];
        $this->raw = $body;
    }

    /**
     * Return a new {@see SearchResult} instance with the hits filtered using `array_filter($this->hits, $callback, ARRAY_FILTER_USE_BOTH)`.
     *
     * The $callback receives both the current hit and the key, in that order.
     *
     * The method DOES not trigger a new search.
     *
     * @return SearchResult
     */
    public function filter(callable $callback): self
    {
        $results = array_filter($this->hits, $callback, ARRAY_FILTER_USE_BOTH);

        $this->hits = $results;
        $this->nbHits = \count($results);

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

    public function getMatches(): int
    {
        return $this->nbHits;
    }

    public function getNbHits(): int
    {
        return \count($this->hits);
    }

    public function getExhaustiveNbHits(): bool
    {
        return $this->exhaustiveNbHits;
    }

    public function getProcessingTimeMs(): int
    {
        return $this->processingTimeMs;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getExhaustiveFacetsCount(): ?bool
    {
        return $this->exhaustiveFacetsCount;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFacetsDistribution(): array
    {
        return $this->facetsDistribution;
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
            'matches' => $this->nbHits,
            'nbHits' => \count($this->hits),
            'exhaustiveNbHits' => $this->exhaustiveNbHits,
            'processingTimeMs' => $this->processingTimeMs,
            'query' => $this->query,
            'exhaustiveFacetsCount' => $this->exhaustiveFacetsCount,
            'facetsDistribution' => $this->facetsDistribution,
        ];
    }

    public function json(): string
    {
        return \json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->hits);
    }

    public function count(): int
    {
        return \count($this->hits);
    }
}
