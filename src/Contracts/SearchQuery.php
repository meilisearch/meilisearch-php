<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class SearchQuery
{
    /**
     * @var non-empty-string|null
     */
    private ?string $indexUid = null;

    private ?string $q = null;

    /**
     * @var list<non-empty-string|list<non-empty-string>>|null
     */
    private ?array $filter = null;

    /**
     * @var list<non-empty-string>|null
     */
    private ?array $locales = null;

    /**
     * @var list<non-empty-string>|null
     */
    private ?array $attributesToRetrieve = null;

    /**
     * @var list<non-empty-string>|null
     */
    private ?array $attributesToCrop = null;

    /**
     * @var positive-int|null
     */
    private ?int $cropLength = null;

    /**
     * @var list<non-empty-string>|null
     */
    private ?array $attributesToHighlight = null;

    private ?string $cropMarker = null;

    private ?string $highlightPreTag = null;

    private ?string $highlightPostTag = null;

    /**
     * @var list<non-empty-string>|null
     */
    private ?array $facets = null;

    private ?bool $showMatchesPosition = null;

    /**
     * @var list<non-empty-string>|null
     */
    private ?array $sort = null;

    /**
     * @var 'last'|'all'|'frequency'|null
     */
    private ?string $matchingStrategy = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $offset = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $limit = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $hitsPerPage = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $page = null;

    /**
     * @var non-empty-list<float|non-empty-list<float>>|null
     */
    private ?array $vector = null;

    private ?HybridSearchOptions $hybrid = null;

    /**
     * @var non-empty-list<non-empty-string>|null
     */
    private ?array $attributesToSearchOn = null;

    private ?bool $showRankingScore = null;

    private ?bool $showRankingScoreDetails = null;

    private ?float $rankingScoreThreshold = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $distinct = null;

    private ?FederationOptions $federationOptions = null;

    /**
     * @return $this
     */
    public function setQuery(string $q): self
    {
        $this->q = $q;

        return $this;
    }

    /**
     * @param list<non-empty-string|list<non-empty-string>> $filter
     *
     * @return $this
     */
    public function setFilter(array $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param list<non-empty-string> $locales
     *
     * @return $this
     */
    public function setLocales(array $locales): self
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAttributesToRetrieve(array $attributesToRetrieve): self
    {
        $this->attributesToRetrieve = $attributesToRetrieve;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAttributesToCrop(array $attributesToCrop): self
    {
        $this->attributesToCrop = $attributesToCrop;

        return $this;
    }

    /**
     * @param positive-int|null $cropLength
     *
     * @return $this
     */
    public function setCropLength(?int $cropLength): self
    {
        $this->cropLength = $cropLength;

        return $this;
    }

    /**
     * @param list<non-empty-string> $attributesToHighlight
     *
     * @return $this
     */
    public function setAttributesToHighlight(array $attributesToHighlight): self
    {
        $this->attributesToHighlight = $attributesToHighlight;

        return $this;
    }

    /**
     * @return $this
     */
    public function setCropMarker(string $cropMarker): self
    {
        $this->cropMarker = $cropMarker;

        return $this;
    }

    /**
     * @return $this
     */
    public function setHighlightPreTag(string $highlightPreTag): self
    {
        $this->highlightPreTag = $highlightPreTag;

        return $this;
    }

    /**
     * @return $this
     */
    public function setHighlightPostTag(string $highlightPostTag): self
    {
        $this->highlightPostTag = $highlightPostTag;

        return $this;
    }

    /**
     * @param list<non-empty-string> $facets
     *
     * @return $this
     */
    public function setFacets(array $facets): self
    {
        $this->facets = $facets;

        return $this;
    }

    /**
     * @return $this
     */
    public function setShowMatchesPosition(?bool $showMatchesPosition): self
    {
        $this->showMatchesPosition = $showMatchesPosition;

        return $this;
    }

    /**
     * @return $this
     */
    public function setShowRankingScore(?bool $showRankingScore): self
    {
        $this->showRankingScore = $showRankingScore;

        return $this;
    }

    /**
     * This is an EXPERIMENTAL feature, which may break without a major version.
     * It's available after Meilisearch v1.3.
     * To enable it properly and use ranking scoring details its required to opt-in through the /experimental-features route.
     *
     * More info: https://www.meilisearch.com/docs/reference/api/experimental-features
     *
     * @param bool $showRankingScoreDetails whether the feature is enabled or not
     *
     * @return $this
     */
    public function setShowRankingScoreDetails(?bool $showRankingScoreDetails): self
    {
        $this->showRankingScoreDetails = $showRankingScoreDetails;

        return $this;
    }

    /**
     * @return $this
     */
    public function setRankingScoreThreshold(?float $rankingScoreThreshold): self
    {
        $this->rankingScoreThreshold = $rankingScoreThreshold;

        return $this;
    }

    /**
     * @param non-empty-string|null $distinct
     *
     * @return $this
     */
    public function setDistinct(?string $distinct): self
    {
        $this->distinct = $distinct;

        return $this;
    }

    /**
     * @return $this
     */
    public function setSort(array $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @param 'last'|'all'|'frequency' $matchingStrategy
     *
     * @return $this
     */
    public function setMatchingStrategy(string $matchingStrategy): self
    {
        $this->matchingStrategy = $matchingStrategy;

        return $this;
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
     * @param non-negative-int|null $limit
     *
     * @return $this
     */
    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param non-negative-int|null $hitsPerPage
     *
     * @return $this
     */
    public function setHitsPerPage(?int $hitsPerPage): self
    {
        $this->hitsPerPage = $hitsPerPage;

        return $this;
    }

    /**
     * @return $this
     */
    public function setPage(?int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return $this
     */
    public function setIndexUid(string $uid): self
    {
        $this->indexUid = $uid;

        return $this;
    }

    /**
     * This option is only available while doing a federated search.
     * If used in another context an error will be returned by Meilisearch.
     *
     * @return $this
     */
    public function setFederationOptions(FederationOptions $federationOptions): self
    {
        $this->federationOptions = $federationOptions;

        return $this;
    }

    /**
     * This is an EXPERIMENTAL feature, which may break without a major version.
     * It's available from Meilisearch v1.3.
     * To enable it properly and use vector store capabilities it's required to activate it through the /experimental-features route.
     *
     * More info: https://www.meilisearch.com/docs/reference/api/experimental-features
     *
     * @param non-empty-list<float|non-empty-list<float>> $vector a multi-level array floats
     *
     * @return $this
     */
    public function setVector(array $vector): self
    {
        $this->vector = $vector;

        return $this;
    }

    /**
     * This is an EXPERIMENTAL feature, which may break without a major version.
     *
     * Set hybrid search options
     * (new HybridSearchOptions())
     *     ->setSemanticRatio(0.8)
     *     ->setEmbedder('manual');
     *
     * @return $this
     */
    public function setHybrid(HybridSearchOptions $hybridOptions): self
    {
        $this->hybrid = $hybridOptions;

        return $this;
    }

    /**
     * @param non-empty-list<non-empty-string> $attributesToSearchOn
     *
     * @return $this
     */
    public function setAttributesToSearchOn(array $attributesToSearchOn): self
    {
        $this->attributesToSearchOn = $attributesToSearchOn;

        return $this;
    }

    /**
     * @return array{
     *     indexUid?: non-empty-string,
     *     q?: string,
     *     filter?: list<non-empty-string|list<non-empty-string>>,
     *     locales?: list<non-empty-string>,
     *     attributesToRetrieve?: list<non-empty-string>,
     *     attributesToCrop?: list<non-empty-string>,
     *     cropLength?: positive-int,
     *     attributesToHighlight?: list<non-empty-string>,
     *     cropMarker?: string,
     *     highlightPreTag?: string,
     *     highlightPostTag?: string,
     *     facets?: list<non-empty-string>,
     *     showMatchesPosition?: bool,
     *     sort?: list<non-empty-string>,
     *     matchingStrategy?: 'last'|'all'|'frequency',
     *     offset?: non-negative-int,
     *     limit?: non-negative-int,
     *     hitsPerPage?: non-negative-int,
     *     page?: non-negative-int,
     *     vector?: non-empty-list<float|non-empty-list<float>>,
     *     hybrid?: array<mixed>,
     *     attributesToSearchOn?: non-empty-list<non-empty-string>,
     *     showRankingScore?: bool,
     *     showRankingScoreDetails?: bool,
     *     rankingScoreThreshold?: float,
     *     distinct?: non-empty-string,
     *     federationOptions?: array<mixed>
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'indexUid' => $this->indexUid,
            'q' => $this->q,
            'filter' => $this->filter,
            'locales' => $this->locales,
            'attributesToRetrieve' => $this->attributesToRetrieve,
            'attributesToCrop' => $this->attributesToCrop,
            'cropLength' => $this->cropLength,
            'attributesToHighlight' => $this->attributesToHighlight,
            'cropMarker' => $this->cropMarker,
            'highlightPreTag' => $this->highlightPreTag,
            'highlightPostTag' => $this->highlightPostTag,
            'facets' => $this->facets,
            'showMatchesPosition' => $this->showMatchesPosition,
            'sort' => $this->sort,
            'matchingStrategy' => $this->matchingStrategy,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'hitsPerPage' => $this->hitsPerPage,
            'page' => $this->page,
            'vector' => $this->vector,
            'hybrid' => null !== $this->hybrid ? $this->hybrid->toArray() : null,
            'attributesToSearchOn' => $this->attributesToSearchOn,
            'showRankingScore' => $this->showRankingScore,
            'showRankingScoreDetails' => $this->showRankingScoreDetails,
            'rankingScoreThreshold' => $this->rankingScoreThreshold,
            'distinct' => $this->distinct,
            'federationOptions' => null !== $this->federationOptions ? $this->federationOptions->toArray() : null,
        ], static function ($item) { return null !== $item; });
    }
}
