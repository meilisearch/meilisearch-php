<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type BaseSearchQueryArray array{
 *     q?: string,
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
 *     hybrid?: array{semanticRatio?: float, embedder?: non-empty-string},
 *     attributesToSearchOn?: non-empty-list<non-empty-string>,
 *     showRankingScore?: bool,
 *     showRankingScoreDetails?: bool,
 *     showPerformanceDetails?: bool,
 *     rankingScoreThreshold?: float,
 *     distinct?: non-empty-string,
 *     retrieveVectors?: bool,
 *     media?: array<mixed>
 * }
 */
abstract class AbstractSearchQuery
{
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

    /**
     * @param array<string, mixed> $data
     */
    protected function hydrateFromArray(array $data): static
    {
        if (\array_key_exists('q', $data)) {
            $this->setQuery($data['q']);
        }
        if (\array_key_exists('filter', $data)) {
            $this->setFilter($data['filter']);
        }
        if (\array_key_exists('locales', $data)) {
            $this->setLocales($data['locales']);
        }
        if (\array_key_exists('attributesToRetrieve', $data)) {
            $this->setAttributesToRetrieve($data['attributesToRetrieve']);
        }
        if (\array_key_exists('attributesToCrop', $data)) {
            $this->setAttributesToCrop($data['attributesToCrop']);
        }
        if (\array_key_exists('cropLength', $data)) {
            $this->setCropLength($data['cropLength']);
        }
        if (\array_key_exists('attributesToHighlight', $data)) {
            $this->setAttributesToHighlight($data['attributesToHighlight']);
        }
        if (\array_key_exists('cropMarker', $data)) {
            $this->setCropMarker($data['cropMarker']);
        }
        if (\array_key_exists('highlightPreTag', $data)) {
            $this->setHighlightPreTag($data['highlightPreTag']);
        }
        if (\array_key_exists('highlightPostTag', $data)) {
            $this->setHighlightPostTag($data['highlightPostTag']);
        }
        if (\array_key_exists('facets', $data)) {
            $this->setFacets($data['facets']);
        }
        if (\array_key_exists('showMatchesPosition', $data)) {
            $this->setShowMatchesPosition($data['showMatchesPosition']);
        }
        if (\array_key_exists('sort', $data)) {
            $this->setSort($data['sort']);
        }
        if (\array_key_exists('matchingStrategy', $data)) {
            $this->setMatchingStrategy($data['matchingStrategy']);
        }
        if (\array_key_exists('offset', $data)) {
            $this->setOffset($data['offset']);
        }
        if (\array_key_exists('limit', $data)) {
            $this->setLimit($data['limit']);
        }
        if (\array_key_exists('hitsPerPage', $data)) {
            $this->setHitsPerPage($data['hitsPerPage']);
        }
        if (\array_key_exists('page', $data)) {
            $this->setPage($data['page']);
        }
        if (\array_key_exists('vector', $data)) {
            $this->setVector($data['vector']);
        }
        if (\array_key_exists('hybrid', $data)) {
            $this->setHybrid(HybridSearchOptions::fromArray($data['hybrid']));
        }
        if (\array_key_exists('attributesToSearchOn', $data)) {
            $this->setAttributesToSearchOn($data['attributesToSearchOn']);
        }
        if (\array_key_exists('showRankingScore', $data)) {
            $this->setShowRankingScore($data['showRankingScore']);
        }
        if (\array_key_exists('showRankingScoreDetails', $data)) {
            $this->setShowRankingScoreDetails($data['showRankingScoreDetails']);
        }
        if (\array_key_exists('showPerformanceDetails', $data)) {
            $this->setShowPerformanceDetails($data['showPerformanceDetails']);
        }
        if (\array_key_exists('rankingScoreThreshold', $data)) {
            $this->setRankingScoreThreshold($data['rankingScoreThreshold']);
        }
        if (\array_key_exists('distinct', $data)) {
            $this->setDistinct($data['distinct']);
        }
        if (\array_key_exists('retrieveVectors', $data)) {
            $this->setRetrieveVectors($data['retrieveVectors']);
        }
        if (\array_key_exists('media', $data)) {
            $this->setMedia($data['media']);
        }

        return $this;
    }

    public function setQuery(?string $q): static
    {
        $this->q = $q;

        return $this;
    }

    /**
     * @param string|list<non-empty-string|list<non-empty-string>> $filter
     */
    public function setFilter(string|array $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @param list<non-empty-string> $locales
     */
    public function setLocales(array $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    public function setAttributesToRetrieve(array $attributesToRetrieve): static
    {
        $this->attributesToRetrieve = $attributesToRetrieve;

        return $this;
    }

    public function setAttributesToCrop(array $attributesToCrop): static
    {
        $this->attributesToCrop = $attributesToCrop;

        return $this;
    }

    /**
     * @param positive-int|null $cropLength
     */
    public function setCropLength(?int $cropLength): static
    {
        $this->cropLength = $cropLength;

        return $this;
    }

    /**
     * @param list<non-empty-string> $attributesToHighlight
     */
    public function setAttributesToHighlight(array $attributesToHighlight): static
    {
        $this->attributesToHighlight = $attributesToHighlight;

        return $this;
    }

    public function setCropMarker(string $cropMarker): static
    {
        $this->cropMarker = $cropMarker;

        return $this;
    }

    public function setHighlightPreTag(string $highlightPreTag): static
    {
        $this->highlightPreTag = $highlightPreTag;

        return $this;
    }

    public function setHighlightPostTag(string $highlightPostTag): static
    {
        $this->highlightPostTag = $highlightPostTag;

        return $this;
    }

    /**
     * @param list<non-empty-string> $facets
     */
    public function setFacets(array $facets): static
    {
        $this->facets = $facets;

        return $this;
    }

    public function setShowMatchesPosition(?bool $showMatchesPosition): static
    {
        $this->showMatchesPosition = $showMatchesPosition;

        return $this;
    }

    public function setShowRankingScore(?bool $showRankingScore): static
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
     */
    public function setShowRankingScoreDetails(?bool $showRankingScoreDetails): static
    {
        $this->showRankingScoreDetails = $showRankingScoreDetails;

        return $this;
    }

    public function setShowPerformanceDetails(?bool $showPerformanceDetails): static
    {
        $this->showPerformanceDetails = $showPerformanceDetails;

        return $this;
    }

    public function setRankingScoreThreshold(?float $rankingScoreThreshold): static
    {
        $this->rankingScoreThreshold = $rankingScoreThreshold;

        return $this;
    }

    /**
     * @param non-empty-string|null $distinct
     */
    public function setDistinct(?string $distinct): static
    {
        $this->distinct = $distinct;

        return $this;
    }

    public function setSort(array $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @param 'last'|'all'|'frequency' $matchingStrategy
     */
    public function setMatchingStrategy(string $matchingStrategy): static
    {
        $this->matchingStrategy = $matchingStrategy;

        return $this;
    }

    /**
     * @param non-negative-int|null $offset
     */
    public function setOffset(?int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param non-negative-int|null $limit
     */
    public function setLimit(?int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param non-negative-int|null $hitsPerPage
     */
    public function setHitsPerPage(?int $hitsPerPage): static
    {
        $this->hitsPerPage = $hitsPerPage;

        return $this;
    }

    public function setPage(?int $page): static
    {
        $this->page = $page;

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
     */
    public function setVector(array $vector): static
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
     */
    public function setHybrid(HybridSearchOptions $hybridOptions): static
    {
        $this->hybrid = $hybridOptions;

        return $this;
    }

    /**
     * @param non-empty-list<non-empty-string> $attributesToSearchOn
     */
    public function setAttributesToSearchOn(array $attributesToSearchOn): static
    {
        $this->attributesToSearchOn = $attributesToSearchOn;

        return $this;
    }

    public function setRetrieveVectors(?bool $retrieveVectors): static
    {
        $this->retrieveVectors = $retrieveVectors;

        return $this;
    }

    public function setMedia(?array $media): static
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return BaseSearchQueryArray
     */
    protected function baseArray(): array
    {
        return array_filter([
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
        ], static function ($item) { return null !== $item; });
    }
}
