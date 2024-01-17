<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Index\Faceting;
use Meilisearch\Contracts\Index\Synonyms;
use Meilisearch\Contracts\Index\TypoTolerance;

trait HandlesSettings
{
    // Settings - Ranking rules

    /**
     * @return list<non-empty-string>
     */
    public function getRankingRules(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/ranking-rules');
    }

    /**
     * @param list<non-empty-string> $rankingRules
     */
    public function updateRankingRules(array $rankingRules): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/ranking-rules', $rankingRules);
    }

    public function resetRankingRules(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/ranking-rules');
    }

    // Settings - Distinct attribute

    /**
     * @return non-empty-string|null
     */
    public function getDistinctAttribute(): ?string
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/distinct-attribute');
    }

    /**
     * @param non-empty-string $distinctAttribute
     */
    public function updateDistinctAttribute(string $distinctAttribute): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/distinct-attribute', $distinctAttribute);
    }

    public function resetDistinctAttribute(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/distinct-attribute');
    }

    // Settings - Searchable attributes

    /**
     * @return list<non-empty-string>
     */
    public function getSearchableAttributes(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/searchable-attributes');
    }

    /**
     * @param list<non-empty-string> $searchableAttributes
     */
    public function updateSearchableAttributes(array $searchableAttributes): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/searchable-attributes', $searchableAttributes);
    }

    public function resetSearchableAttributes(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/searchable-attributes');
    }

    // Settings - Displayed attributes

    /**
     * @return list<non-empty-string>
     */
    public function getDisplayedAttributes(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/displayed-attributes');
    }

    /**
     * @param list<non-empty-string> $displayedAttributes
     */
    public function updateDisplayedAttributes(array $displayedAttributes): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/displayed-attributes', $displayedAttributes);
    }

    public function resetDisplayedAttributes(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/displayed-attributes');
    }

    // Settings - Faceting

    /**
     * @return array{maxValuesPerFacet: int, sortFacetValuesBy: array<non-empty-string, 'count'|'alpha'>}
     */
    public function getFaceting(): array
    {
        return (new Faceting($this->http->get(self::PATH.'/'.$this->uid.'/settings/faceting')))
            ->getIterator()->getArrayCopy();
    }

    /**
     * @param array{maxValuesPerFacet?: int, sortFacetValuesBy?: array<non-empty-string, 'count'|'alpha'>} $faceting
     */
    public function updateFaceting(array $faceting): array
    {
        return $this->http->patch(self::PATH.'/'.$this->uid.'/settings/faceting', $faceting);
    }

    public function resetFaceting(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/faceting');
    }

    // Settings - Pagination

    /**
     * @return array{maxTotalHits: positive-int}
     */
    public function getPagination(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/pagination');
    }

    /**
     * @param array{maxTotalHits: positive-int} $pagination
     */
    public function updatePagination(array $pagination): array
    {
        return $this->http->patch(self::PATH.'/'.$this->uid.'/settings/pagination', $pagination);
    }

    public function resetPagination(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/pagination');
    }

    // Settings - Stop-words

    /**
     * @return list<non-empty-string>
     */
    public function getStopWords(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/stop-words');
    }

    /**
     * @param list<non-empty-string> $stopWords
     */
    public function updateStopWords(array $stopWords): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/stop-words', $stopWords);
    }

    public function resetStopWords(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/stop-words');
    }

    // Settings - Synonyms

    /**
     * @return array<non-empty-string, list<non-empty-string>>
     */
    public function getSynonyms(): array
    {
        return (new Synonyms($this->http->get(self::PATH.'/'.$this->uid.'/settings/synonyms')))
            ->getIterator()->getArrayCopy();
    }

    /**
     * @param array<non-empty-string, list<non-empty-string>> $synonyms
     */
    public function updateSynonyms(array $synonyms): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/synonyms', new Synonyms($synonyms));
    }

    public function resetSynonyms(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/synonyms');
    }

    // Settings - Filterable Attributes

    /**
     * @return list<non-empty-string>
     */
    public function getFilterableAttributes(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/filterable-attributes');
    }

    /**
     * @param list<non-empty-string> $filterableAttributes
     */
    public function updateFilterableAttributes(array $filterableAttributes): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/filterable-attributes', $filterableAttributes);
    }

    public function resetFilterableAttributes(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/filterable-attributes');
    }

    // Settings - Sortable Attributes

    /**
     * @return list<non-empty-string>
     */
    public function getSortableAttributes(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/sortable-attributes');
    }

    /**
     * @param list<non-empty-string> $sortableAttributes
     */
    public function updateSortableAttributes(array $sortableAttributes): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/sortable-attributes', $sortableAttributes);
    }

    public function resetSortableAttributes(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/sortable-attributes');
    }

    // Settings - Typo Tolerance

    /**
     * @return array{
     *     enabled: bool,
     *     minWordSizeForTypos: array{oneTypo: int, twoTypos: int},
     *     disableOnWords: list<non-empty-string>,
     *     disableOnAttributes: list<non-empty-string>
     * }
     */
    public function getTypoTolerance(): array
    {
        return (new TypoTolerance($this->http->get(self::PATH.'/'.$this->uid.'/settings/typo-tolerance')))
            ->getIterator()->getArrayCopy();
    }

    /**
     * @param array{
     *     enabled: bool,
     *     minWordSizeForTypos: array{oneTypo: int, twoTypos: int},
     *     disableOnWords: list<non-empty-string>,
     *     disableOnAttributes: list<non-empty-string>
     * } $typoTolerance
     */
    public function updateTypoTolerance(array $typoTolerance): array
    {
        return $this->http->patch(self::PATH.'/'.$this->uid.'/settings/typo-tolerance', new TypoTolerance($typoTolerance));
    }

    public function resetTypoTolerance(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/typo-tolerance');
    }

    // Settings - Word dictionary

    /**
     * @return list<non-empty-string>
     */
    public function getDictionary(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/dictionary');
    }

    /**
     * @param list<non-empty-string> $wordDictionary
     */
    public function updateDictionary(array $wordDictionary): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/dictionary', $wordDictionary);
    }

    public function resetDictionary(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/dictionary');
    }

    // Settings - Separator tokens

    public function getSeparatorTokens(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/separator-tokens');
    }

    /**
     * @param list<non-empty-string> $separatorTokens
     */
    public function updateSeparatorTokens(array $separatorTokens): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/separator-tokens', $separatorTokens);
    }

    public function resetSeparatorTokens(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/separator-tokens');
    }

    // Settings - Non-Separator tokens

    /**
     * @return list<non-empty-string>
     */
    public function getNonSeparatorTokens(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/non-separator-tokens');
    }

    /**
     * @param list<non-empty-string> $nonSeparatorTokens
     */
    public function updateNonSeparatorTokens(array $nonSeparatorTokens): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/non-separator-tokens', $nonSeparatorTokens);
    }

    public function resetNonSeparatorTokens(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/non-separator-tokens');
    }

    // Settings - proximityPrecision

    /**
     * @return 'byWord'|'byAttribute'
     */
    public function getProximityPrecision(): string
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/proximity-precision');
    }

    /**
     * @param 'byWord'|'byAttribute' $type
     */
    public function updateProximityPrecision(string $type): array
    {
        return $this->http->put(self::PATH.'/'.$this->uid.'/settings/proximity-precision', $type);
    }

    public function resetProximityPrecision(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/proximity-precision');
    }

    // Settings - Experimental: Embedders (hybrid search)

    public function getEmbedders(): ?array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/embedders');
    }

    public function updateEmbedders(array $embedders): array
    {
        return $this->http->patch(self::PATH.'/'.$this->uid.'/settings/embedders', $embedders);
    }

    public function resetEmbedders(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings/embedders');
    }
}
