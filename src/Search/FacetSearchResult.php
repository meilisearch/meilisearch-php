<?php

declare(strict_types=1);

namespace Meilisearch\Search;

/**
 * @implements \IteratorAggregate<int, array<string, mixed>>
 */
final class FacetSearchResult implements \Countable, \IteratorAggregate
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $facetHits;

    /**
     * @var non-negative-int
     */
    private int $processingTimeMs;

    private ?string $facetQuery;

    /**
     * @var array<string, mixed>
     */
    private array $raw;

    /**
     * @param array{
     *     facetHits: array<int, array<string, mixed>>,
     *     facetQuery: string|null,
     *     processingTimeMs: non-negative-int
     * } $body
     */
    public function __construct(array $body)
    {
        $this->facetHits = $body['facetHits'];
        $this->facetQuery = $body['facetQuery'];
        $this->processingTimeMs = $body['processingTimeMs'];
        $this->raw = $body;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFacetHits(): array
    {
        return $this->facetHits;
    }

    /**
     * @return non-negative-int
     */
    public function getProcessingTimeMs(): int
    {
        return $this->processingTimeMs;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * @return array{
     *     facetHits: array<int, array<string, mixed>>,
     *     facetQuery: string|null,
     *     processingTimeMs: non-negative-int
     * }
     */
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
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
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
