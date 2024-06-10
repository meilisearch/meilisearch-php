<?php

declare(strict_types=1);

namespace Meilisearch\Search;

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
        $this->hits = $body['hits'] ?? [];
        $this->hitsCount = \count($body['hits']);
        $this->processingTimeMs = $body['processingTimeMs'];
        $this->offset = $body['offset'];
        $this->limit = $body['limit'];
        $this->estimatedTotalHits = $body['estimatedTotalHits'];
    }

    /**
     * Return a new {@see SearchResult} instance.
     *
     * The $options parameter is an array, and the following keys are accepted:
     * - transformHits (callable)
     *
     * The method does NOT trigger a new search.
     */
    public function applyOptions($options): self
    {
        if (\array_key_exists('transformHits', $options) && \is_callable($options['transformHits'])) {
            $this->transformHits($options['transformHits']);
        }

        return $this;
    }

    public function transformHits(callable $callback): self
    {
        $this->hits = $callback($this->hits);
        $this->hitsCount = \count($this->hits);

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

    public function toArray(): array
    {
        $arr = [
            'id' => $this->id,
            'hits' => $this->hits,
            'hitsCount' => $this->hitsCount,
            'processingTimeMs' => $this->processingTimeMs,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'estimatedTotalHits' => $this->estimatedTotalHits,
        ];

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

    public function count(): int
    {
        return $this->hitsCount;
    }
}
