<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type MultiSearchQueryArray array{
 *     indexUid?: non-empty-string,
 *     q?: string|null,
 *     filter?: string|list<non-empty-string|list<non-empty-string>>,
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
 *     showPerformanceDetails?: bool,
 *     rankingScoreThreshold?: float,
 *     distinct?: non-empty-string,
 *     retrieveVectors?: bool,
 *     media?: array<mixed>,
 *     federationOptions?: array<mixed>
 * }
 */
class MultiSearchQuery
{
    /**
     * @var non-empty-string|null
     */
    private ?string $indexUid = null;

    private ?string $q = null;

    /**
     * @var string|list<non-empty-string|list<non-empty-string>>|null
     */
    private string|array|null $filter = null;

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

    private ?bool $showPerformanceDetails = null;

    private ?float $rankingScoreThreshold = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $distinct = null;

    private ?bool $retrieveVectors = null;

    private ?array $media = null;

    private ?FederationOptions $federationOptions = null;

    /**
     * @param MultiSearchQueryArray $data
     */
    public static function fromArray(array $data): self
    {
        $query = new self();

        if (\array_key_exists('indexUid', $data)) {
            $query->setIndexUid($data['indexUid']);
        }
        if (\array_key_exists('q', $data)) {
            $query->setQuery($data['q']);
        }
        if (\array_key_exists('filter', $data)) {
            $query->setFilter($data['filter']);
        }
        if (\array_key_exists('locales', $data)) {
            $query->setLocales($data['locales']);
        }
        if (\array_key_exists('attributesToRetrieve', $data)) {
            $query->setAttributesToRetrieve($data['attributesToRetrieve']);
        }
        if (\array_key_exists('attributesToCrop', $data)) {
            $query->setAttributesToCrop($data['attributesToCrop']);
        }
        if (\array_key_exists('cropLength', $data)) {
            $query->setCropLength($data['cropLength']);
        }
        if (\array_key_exists('attributesToHighlight', $data)) {
            $query->setAttributesToHighlight($data['attributesToHighlight']);
        }
        if (\array_key_exists('cropMarker', $data)) {
            $query->setCropMarker($data['cropMarker']);
        }
        if (\array_key_exists('highlightPreTag', $data)) {
            $query->setHighlightPreTag($data['highlightPreTag']);
        }
        if (\array_key_exists('highlightPostTag', $data)) {
            $query->setHighlightPostTag($data['highlightPostTag']);
        }
        if (\array_key_exists('facets', $data)) {
            $query->setFacets($data['facets']);
        }
        if (\array_key_exists('showMatchesPosition', $data)) {
            $query->setShowMatchesPosition($data['showMatchesPosition']);
        }
        if (\array_key_exists('sort', $data)) {
            $query->setSort($data['sort']);
        }
        if (\array_key_exists('matchingStrategy', $data)) {
            $query->setMatchingStrategy($data['matchingStrategy']);
        }
        if (\array_key_exists('offset', $data)) {
            $query->setOffset($data['offset']);
        }
        if (\array_key_exists('limit', $data)) {
            $query->setLimit($data['limit']);
        }
        if (\array_key_exists('hitsPerPage', $data)) {
            $query->setHitsPerPage($data['hitsPerPage']);
        }
        if (\array_key_exists('page', $data)) {
            $query->setPage($data['page']);
        }
        if (\array_key_exists('vector', $data)) {
            $query->setVector($data['vector']);
        }
        if (\array_key_exists('hybrid', $data)) {
            $query->setHybrid(HybridSearchOptions::fromArray($data['hybrid']));
        }
        if (\array_key_exists('attributesToSearchOn', $data)) {
            $query->setAttributesToSearchOn($data['attributesToSearchOn']);
        }
        if (\array_key_exists('showRankingScore', $data)) {
            $query->setShowRankingScore($data['showRankingScore']);
        }
        if (\array_key_exists('showRankingScoreDetails', $data)) {
            $query->setShowRankingScoreDetails($data['showRankingScoreDetails']);
        }
        if (\array_key_exists('showPerformanceDetails', $data)) {
            $query->setShowPerformanceDetails($data['showPerformanceDetails']);
        }
        if (\array_key_exists('rankingScoreThreshold', $data)) {
            $query->setRankingScoreThreshold($data['rankingScoreThreshold']);
        }
        if (\array_key_exists('distinct', $data)) {
            $query->setDistinct($data['distinct']);
        }
        if (\array_key_exists('retrieveVectors', $data)) {
            $query->setRetrieveVectors($data['retrieveVectors']);
        }
        if (\array_key_exists('media', $data)) {
            $query->setMedia($data['media']);
        }
        if (\array_key_exists('federationOptions', $data)) {
            $query->setFederationOptions(FederationOptions::fromArray($data['federationOptions']));
        }

        return $query;
    }

    /**
     * @return $this
     */
    public function setQuery(?string $q): self
    {
        $this->q = $q;

        return $this;
    }

    /**
     * @param string|list<non-empty-string|list<non-empty-string>> $filter
     *
     * @return $this
     */
    public function setFilter(string|array $filter): self
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
     * More info: https://www.meilisearch.com/docs/reference/api/experimental_features
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
    public function setShowPerformanceDetails(?bool $showPerformanceDetails): self
    {
        $this->showPerformanceDetails = $showPerformanceDetails;

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
     * More info: https://www.meilisearch.com/docs/reference/api/experimental_features
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
    public function setMedia(?array $media): self
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return MultiSearchQueryArray
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
            'showPerformanceDetails' => $this->showPerformanceDetails,
            'rankingScoreThreshold' => $this->rankingScoreThreshold,
            'distinct' => $this->distinct,
            'retrieveVectors' => $this->retrieveVectors,
            'media' => $this->media,
            'federationOptions' => null !== $this->federationOptions ? $this->federationOptions->toArray() : null,
        ], static function ($item) { return null !== $item; });
    }
}
