<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class SearchQuery
{
    private string $indexUid;

    private string $q;
    private array $filter;
    private array $attributesToRetrieve;
    private array $attributesToCrop;
    private ?int $cropLength;
    private array $attributesToHighlight;
    private string $cropMarker;
    private string $highlightPreTag;
    private string $highlightPostTag;
    private array $facets;
    private ?bool $showMatchesPosition;
    private array $sort;
    private string $matchingStrategy;
    private ?int $offset;
    private ?int $limit;
    private ?int $hitsPerPage;
    private ?int $page;

    public function setQuery(string $q): SearchQuery
    {
        $this->q = $q;

        return $this;
    }

    public function setFilter(array $filter): SearchQuery
    {
        $this->filter = $filter;

        return $this;
    }

    public function setAttributesToRetrieve(array $attributesToRetrieve): SearchQuery
    {
        $this->attributesToRetrieve = $attributesToRetrieve;

        return $this;
    }

    public function setAttributesToCrop(array $attributesToCrop): SearchQuery
    {
        $this->attributesToCrop = $attributesToCrop;

        return $this;
    }

    public function setCropLength(?int $cropLength): SearchQuery
    {
        $this->cropLength = $cropLength;

        return $this;
    }

    public function setAttributesToHighlight(array $attributesToHighlight): SearchQuery
    {
        $this->attributesToHighlight = $attributesToHighlight;

        return $this;
    }

    public function setCropMarker(string $cropMarker): SearchQuery
    {
        $this->cropMarker = $cropMarker;

        return $this;
    }

    public function setHighlightPreTag(string $highlightPreTag): SearchQuery
    {
        $this->highlightPreTag = $highlightPreTag;

        return $this;
    }

    public function setHighlightPostTag(string $highlightPostTag): SearchQuery
    {
        $this->highlightPostTag = $highlightPostTag;

        return $this;
    }

    public function setFacets(array $facets): SearchQuery
    {
        $this->facets = $facets;

        return $this;
    }

    public function setShowMatchesPosition(?bool $showMatchesPosition): SearchQuery
    {
        $this->showMatchesPosition = $showMatchesPosition;

        return $this;
    }

    public function setSort(array $sort): SearchQuery
    {
        $this->sort = $sort;

        return $this;
    }

    public function setMatchingStrategy(string $matchingStrategy): SearchQuery
    {
        $this->matchingStrategy = $matchingStrategy;

        return $this;
    }

    public function setOffset(?int $offset): SearchQuery
    {
        $this->offset = $offset;

        return $this;
    }

    public function setLimit(?int $limit): SearchQuery
    {
        $this->limit = $limit;

        return $this;
    }

    public function setHitsPerPage(?int $hitsPerPage): SearchQuery
    {
        $this->hitsPerPage = $hitsPerPage;

        return $this;
    }

    public function setPage(?int $page): SearchQuery
    {
        $this->page = $page;

        return $this;
    }

    public function setIndexUid(string $uid): SearchQuery
    {
        $this->indexUid = $uid;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'indexUid' => $this->indexUid ?? null,
            'q' => $this->q ?? null,
            'filter' => $this->filter ?? null,
            'attributesToRetrieve' => $this->attributesToRetrieve ?? null,
            'attributesToCrop' => $this->attributesToCrop ?? null,
            'cropLength' => $this->cropLength ?? null,
            'attributesToHighlight' => $this->attributesToHighlight ?? null,
            'cropMarker' => $this->cropMarker ?? null,
            'highlightPreTag' => $this->highlightPreTag ?? null,
            'highlightPostTag' => $this->highlightPostTag ?? null,
            'facets' => $this->facets ?? null,
            'showMatchesPosition' => $this->showMatchesPosition ?? null,
            'sort' => $this->sort ?? null,
            'matchingStrategy' => $this->matchingStrategy ?? null,
            'offset' => $this->offset ?? null,
            'limit' => $this->limit ?? null,
            'hitsPerPage' => $this->hitsPerPage ?? null,
            'page' => $this->page ?? null,
        ], function ($item) { return null !== $item; });
    }
}
