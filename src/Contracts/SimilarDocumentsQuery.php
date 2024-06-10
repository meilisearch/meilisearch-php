<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class SimilarDocumentsQuery
{
    private int|string $id;
    private ?int $offset = null;
    private ?int $limit = null;
    private ?string $embedder = null;
    private ?array $attributesToRetrieve = null;
    private ?bool $showRankingScore = null;
    private ?bool $showRankingScoreDetails = null;
    private ?array $filter = null;

    public function setId(string|int $id): SimilarDocumentsQuery
    {
        $this->id = $id;

        return $this;
    }

    public function setOffset(?int $offset): SimilarDocumentsQuery
    {
        $this->offset = $offset;

        return $this;
    }

    public function setLimit(?int $limit): SimilarDocumentsQuery
    {
        $this->limit = $limit;

        return $this;
    }

    public function setFilter(array $filter): SimilarDocumentsQuery
    {
        $this->filter = $filter;

        return $this;
    }

    public function setEmbedder(string $embedder): SimilarDocumentsQuery
    {
        $this->embedder = $embedder;

        return $this;
    }

    public function setAttributesToRetrieve(array $attributesToRetrieve): SimilarDocumentsQuery
    {
        $this->attributesToRetrieve = $attributesToRetrieve;

        return $this;
    }

    public function setShowRankingScore(?bool $showRankingScore): SimilarDocumentsQuery
    {
        $this->showRankingScore = $showRankingScore;

        return $this;
    }

    public function setShowRankingScoreDetails(?bool $showRankingScoreDetails): SimilarDocumentsQuery
    {
        $this->showRankingScoreDetails = $showRankingScoreDetails;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'filter' => $this->filter,
            'embedder' => $this->embedder,
            'attributesToRetrieve' => $this->attributesToRetrieve,
            'showRankingScore' => $this->showRankingScore,
            'showRankingScoreDetails' => $this->showRankingScoreDetails,
        ], function ($item) { return null !== $item; });
    }
}
