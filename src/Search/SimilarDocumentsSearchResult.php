<?php

declare(strict_types=1);

namespace Meilisearch\Search;

/**
 * @implements \IteratorAggregate<array<int, array<string, mixed>>>
 */
class SimilarDocumentsSearchResult implements \Countable, \IteratorAggregate
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
    private string $id;

    public function __construct(array $body)
    {
        $this->id = $body['id'];
        $this->hits = $body['hits'];
        $this->hitsCount = \count($body['hits']);
        $this->processingTimeMs = $body['processingTimeMs'];
        $this->offset = $body['offset'];
        $this->limit = $body['limit'];
        $this->estimatedTotalHits = $body['estimatedTotalHits'];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getHit(int $key): ?array
    {
        return $this->hits[$key];
    }

    /**
     * @return array<int, array<string, mixed>>
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

    public function getEstimatedTotalHits(): int
    {
        return $this->estimatedTotalHits;
    }

    public function getProcessingTimeMs(): int
    {
        return $this->processingTimeMs;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getHitsCount(): int
    {
        return $this->hitsCount;
    }

    /**
     * Converts the SimilarDocumentsSearchResult to an array representation.
     *
     * @return array<string, mixed>
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
        ];
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->hits);
    }

    public function count(): int
    {
        return $this->hitsCount;
    }
}
