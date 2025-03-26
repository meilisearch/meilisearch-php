<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class FacetSearchQuery
{
    /**
     * @var non-empty-string|null
     */
    private ?string $facetName = null;

    /**
     * @var non-empty-string|null
     */
    private ?string $facetQuery = null;

    private ?string $q = null;

    /**
     * @var list<non-empty-string|list<non-empty-string>>|null
     */
    private ?array $filter = null;

    /**
     * @var 'last'|'all'|'frequency'|null
     */
    private ?string $matchingStrategy = null;

    /**
     * @var non-empty-list<non-empty-string>|null
     */
    private ?array $attributesToSearchOn = null;

    private ?bool $exhaustiveFacetsCount = null;

    /**
     * @return $this
     */
    public function setFacetName(string $facetName): self
    {
        $this->facetName = $facetName;

        return $this;
    }

    /**
     * @return $this
     */
    public function setFacetQuery(string $facetQuery): self
    {
        $this->facetQuery = $facetQuery;

        return $this;
    }

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
    public function setExhaustiveFacetsCount(bool $exhaustiveFacetsCount): self
    {
        $this->exhaustiveFacetsCount = $exhaustiveFacetsCount;

        return $this;
    }

    /**
     * @return array{
     *     facetName?: non-empty-string,
     *     facetQuery?: non-empty-string,
     *     q?: string,
     *     filter?: list<non-empty-string|list<non-empty-string>>,
     *     matchingStrategy?: 'last'|'all'|'frequency'|null,
     *     attributesToSearchOn?: non-empty-list<non-empty-string>,
     *     exhaustiveFacetsCount?: bool
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'facetName' => $this->facetName,
            'facetQuery' => $this->facetQuery,
            'q' => $this->q,
            'filter' => $this->filter,
            'matchingStrategy' => $this->matchingStrategy,
            'attributesToSearchOn' => $this->attributesToSearchOn,
            'exhaustiveFacetsCount' => $this->exhaustiveFacetsCount,
        ], static function ($item) { return null !== $item; });
    }
}
