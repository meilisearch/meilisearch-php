<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class MultiSearchFederation
{
    /**
     * @var non-negative-int|null
     */
    private ?int $limit = null;

    /**
     * @var non-negative-int|null
     */
    private ?int $offset = null;

    /**
     * @var array<non-empty-string, list<non-empty-string>>|null
     */
    private ?array $facetsByIndex = null;

    /**
     * @var array{maxValuesPerFacet: positive-int}|null
     */
    private ?array $mergeFacets = null;

    /**
     * @param non-negative-int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param non-negative-int $offset
     *
     * @return $this
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param array<non-empty-string, list<non-empty-string>> $facetsByIndex
     *
     * @return $this
     */
    public function setFacetsByIndex(array $facetsByIndex): self
    {
        $this->facetsByIndex = $facetsByIndex;

        return $this;
    }

    /**
     * @param array{maxValuesPerFacet: positive-int} $mergeFacets
     *
     * @return $this
     */
    public function setMergeFacets(array $mergeFacets): self
    {
        $this->mergeFacets = $mergeFacets;

        return $this;
    }

    /**
     * @return array{
     *     limit?: non-negative-int,
     *     offset?: non-negative-int,
     *     facetsByIndex?: array<non-empty-string, list<non-empty-string>>,
     *     mergeFacets?: array{maxValuesPerFacet: positive-int},
     * }
     */
    public function toArray(): array
    {
        return array_filter([
            'limit' => $this->limit,
            'offset' => $this->offset,
            'facetsByIndex' => $this->facetsByIndex,
            'mergeFacets' => $this->mergeFacets,
        ], static function ($item) { return null !== $item; });
    }
}
