<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class SimilarDocumentsQuery
{
    /**
     * @var int|non-empty-string
     */
    private int|string $id;

    /**
     * @var non-empty-string
     */
    private string $embedder;

    /**
     * @var non-negative-int|null
     */
    private ?int $offset = null;

    /**
     * @var positive-int|null
     */
    private ?int $limit = null;

    /**
     * @var list<non-empty-string>|null
     */
    private ?array $attributesToRetrieve = null;

    private ?bool $showRankingScore = null;

    private ?bool $showRankingScoreDetails = null;

    private ?bool $showPerformanceDetails = null;

    private ?bool $retrieveVectors = null;

    /**
     * @var array<int, array<int, string>|string>|null
     */
    private ?array $filter = null;

    private int|float|null $rankingScoreThreshold = null;

    /**
     * @param int|non-empty-string $id
     * @param non-empty-string     $embedder
     */
    public function __construct(int|string $id, string $embedder)
    {
        $this->id = $id;
        $this->embedder = $embedder;
    }

    /**
     * @param non-negative-int|null $offset
     *
     * @return $this
     */
    public function setOffset(?int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param positive-int|null $limit
     *
     * @return $this
     */
    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param array<int, array<int, string>|string> $filter an array of arrays representing filter conditions
     *
     * @return $this
     */
    public function setFilter(array $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param list<non-empty-string> $attributesToRetrieve an array of attribute names to retrieve
     *
     * @return $this
     */
    public function setAttributesToRetrieve(array $attributesToRetrieve): self
    {
        $this->attributesToRetrieve = $attributesToRetrieve;

        return $this;
    }

    /**
     * @param bool|null $showRankingScore boolean value to show ranking score
     *
     * @return $this
     */
    public function setShowRankingScore(?bool $showRankingScore): self
    {
        $this->showRankingScore = $showRankingScore;

        return $this;
    }

    /**
     * @param bool|null $showRankingScoreDetails boolean value to show ranking score details
     *
     * @return $this
     */
    public function setShowRankingScoreDetails(?bool $showRankingScoreDetails): self
    {
        $this->showRankingScoreDetails = $showRankingScoreDetails;

        return $this;
    }

    /**
     * @param bool|null $showPerformanceDetails boolean value to show performance details
     *
     * @return $this
     */
    public function setShowPerformanceDetails(?bool $showPerformanceDetails): self
    {
        $this->showPerformanceDetails = $showPerformanceDetails;

        return $this;
    }

    /**
     * @param bool|null $retrieveVectors boolean value to show _vector details
     *
     * @return $this
     */
    public function setRetrieveVectors(?bool $retrieveVectors): self
    {
        $this->retrieveVectors = $retrieveVectors;

        return $this;
    }

    /**
     * @return $this
     */
    public function setRankingScoreThreshold(int|float|null $rankingScoreThreshold): self
    {
        $this->rankingScoreThreshold = $rankingScoreThreshold;

        return $this;
    }

    /**
     * @return array{
     *     id: int|non-empty-string,
     *     embedder: non-empty-string,
     *     offset?: non-negative-int,
     *     limit?: positive-int,
     *     filter?: array<int, array<int, string>|string>,
     *     attributesToRetrieve?: list<non-empty-string>,
     *     showRankingScore?: bool,
     *     showRankingScoreDetails?: bool,
     *     showPerformanceDetails?: bool,
     *     retrieveVectors?: bool,
     *     rankingScoreThreshold?: int|float
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'embedder' => $this->embedder,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'filter' => $this->filter,
            'attributesToRetrieve' => $this->attributesToRetrieve,
            'showRankingScore' => $this->showRankingScore,
            'showRankingScoreDetails' => $this->showRankingScoreDetails,
            'showPerformanceDetails' => $this->showPerformanceDetails,
            'retrieveVectors' => $this->retrieveVectors,
            'rankingScoreThreshold' => $this->rankingScoreThreshold,
        ], static function ($item) {return null !== $item; });
    }
}
