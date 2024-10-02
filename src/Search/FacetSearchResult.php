<?php

declare(strict_types=1);

namespace Meilisearch\Search;

/**
 * @implements \IteratorAggregate<array<int, array<string, mixed>>>
 */
class FacetSearchResult implements \Countable, \IteratorAggregate
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $facetHits;
    private int $processingTimeMs;
    private ?string $facetQuery;

    public function __construct(array $body)
    {
        $this->facetHits = $body['facetHits'] ?? [];
        $this->facetQuery = $body['facetQuery'];
        $this->processingTimeMs = $body['processingTimeMs'];
    }

    /**
     * @return array<int, array>
     */
    public function getFacetHits(): array
    {
        return $this->facetHits;
    }

    public function getProcessingTimeMs(): int
    {
        return $this->processingTimeMs;
    }

    public function toArray(): array
    {
        return [
            'facetHits' => $this->facetHits,
            'facetQuery' => $this->facetQuery,
            'processingTimeMs' => $this->processingTimeMs,
        ];
    }

    public function toJSON(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->facetHits);
    }

    public function count(): int
    {
        return \count($this->facetHits);
    }
}
