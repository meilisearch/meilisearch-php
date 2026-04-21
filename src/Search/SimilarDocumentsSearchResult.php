<?php

declare(strict_types=1);

namespace Meilisearch\Search;

/**
 * @implements \IteratorAggregate<int, array<string, mixed>>
 */
final class SimilarDocumentsSearchResult implements \Countable, \IteratorAggregate
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

    /**
     * @var non-negative-int
     */
    private int $hitsCount;

    /**
     * @var non-negative-int
     */
    private int $offset;

    /**
     * @var non-negative-int
     */
    private int $limit;

    /**
     * @var non-negative-int
     */
    private int $processingTimeMs;

    /**
     * @var int|non-empty-string
     */
    private int|string $id;

    /**
     * @var array<string, string>|null
     */
    private ?array $performanceDetails;

    /**
     * @var array<string, mixed>
     */
    private array $raw;

    /**
     * @param array{
     *     id: int|non-empty-string,
     *     hits: array<int, array<string, mixed>>,
     *     hitsCount: non-negative-int,
     *     processingTimeMs: non-negative-int,
     *     offset: non-negative-int,
     *     limit: non-negative-int,
     *     estimatedTotalHits: non-negative-int,
     *     performanceDetails?: array<string, string>|null,
     * } $body
     */
    public function __construct(array $body)
    {
        $this->id = $body['id'];
        $this->hits = $body['hits'];
        $this->hitsCount = \count($body['hits']);
        $this->processingTimeMs = $body['processingTimeMs'];
        $this->offset = $body['offset'];
        $this->limit = $body['limit'];
        $this->estimatedTotalHits = $body['estimatedTotalHits'];
        $this->performanceDetails = $body['performanceDetails'] ?? null;
        $this->raw = $body;
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
     * @return array<int, array<string, mixed>>
     */
    public function getHits(): array
    {
        return $this->hits;
    }

    /**
     * @return non-negative-int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return non-negative-int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return non-negative-int
     */
    public function getEstimatedTotalHits(): int
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

    /**
     * @return int|non-empty-string
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    /**
     * @return non-negative-int
     */
    public function getHitsCount(): int
    {
        return $this->hitsCount;
    }

    /**
     * @return array<string, string>|null
     */
    public function getPerformanceDetails(): ?array
    {
        return $this->performanceDetails;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * Converts the SimilarDocumentsSearchResult to an array representation.
     *
     * @return array{
     *     id: int|non-empty-string,
     *     hits: array<int, array<string, mixed>>,
     *     hitsCount: non-negative-int,
     *     processingTimeMs: non-negative-int,
     *     offset: non-negative-int,
     *     limit: non-negative-int,
     *     estimatedTotalHits: non-negative-int,
     *     performanceDetails?: array<string, string>|null,
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'hits' => $this->hits,
            'hitsCount' => $this->hitsCount,
            'processingTimeMs' => $this->processingTimeMs,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'estimatedTotalHits' => $this->estimatedTotalHits,
            'performanceDetails' => $this->performanceDetails,
        ];
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->hits);
    }

    /**
     * @return non-negative-int
     */
    public function count(): int
    {
        return $this->hitsCount;
    }
}
