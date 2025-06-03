<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\Index\Faceting;
use Meilisearch\Contracts\Index\Synonyms;
use Meilisearch\Contracts\Index\TypoTolerance;
use Meilisearch\Contracts\Task;
use Meilisearch\Endpoints\Tasks;

use function Meilisearch\partial;

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
    public function updateRankingRules(array $rankingRules): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/ranking-rules', $rankingRules), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetRankingRules(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/ranking-rules'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateDistinctAttribute(string $distinctAttribute): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/distinct-attribute', $distinctAttribute), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetDistinctAttribute(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/distinct-attribute'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateSearchableAttributes(array $searchableAttributes): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/searchable-attributes', $searchableAttributes), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetSearchableAttributes(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/searchable-attributes'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateDisplayedAttributes(array $displayedAttributes): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/displayed-attributes', $displayedAttributes), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetDisplayedAttributes(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/displayed-attributes'), partial(Tasks::waitTask(...), $this->http));
    }

    // Settings - Localized attributes

    /**
     * @return list<array{attributePatterns: list<string>, locales: list<non-empty-string>}>|null
     */
    public function getLocalizedAttributes(): ?array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/localized-attributes');
    }

    /**
     * @param list<array{attributePatterns: list<string>, locales: list<non-empty-string>}> $localizedAttributes
     */
    public function updateLocalizedAttributes(array $localizedAttributes): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/localized-attributes', $localizedAttributes), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetLocalizedAttributes(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/localized-attributes'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateFaceting(array $faceting): Task
    {
        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid.'/settings/faceting', $faceting), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetFaceting(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/faceting'), partial(Tasks::waitTask(...), $this->http));
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
    public function updatePagination(array $pagination): Task
    {
        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid.'/settings/pagination', $pagination), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetPagination(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/pagination'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateStopWords(array $stopWords): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/stop-words', $stopWords), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetStopWords(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/stop-words'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateSynonyms(array $synonyms): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/synonyms', new Synonyms($synonyms)), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetSynonyms(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/synonyms'), partial(Tasks::waitTask(...), $this->http));
    }

    // Settings - Filterable Attributes

    /**
     * @return list<non-empty-string|array{
     *     attributePatterns: list<non-empty-string>,
     *     features?: array{
     *         facetSearch: bool,
     *         filter: array{equality: bool, comparison: bool}
     *     }
     * }>
     */
    public function getFilterableAttributes(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/filterable-attributes');
    }

    /**
     * @param list<non-empty-string|array{
     *   attributePatterns: list<non-empty-string>,
     *   features?: array{facetSearch: bool, filter: array{equality: bool, comparison: bool}}
     * }> $filterableAttributes
     *
     * Note: When attributePatterns contains '_geo', the features field is ignored
     */
    public function updateFilterableAttributes(array $filterableAttributes): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/filterable-attributes', $filterableAttributes), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetFilterableAttributes(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/filterable-attributes'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateSortableAttributes(array $sortableAttributes): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/sortable-attributes', $sortableAttributes), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetSortableAttributes(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/sortable-attributes'), partial(Tasks::waitTask(...), $this->http));
    }

    // Settings - Typo Tolerance

    /**
     * @return array{
     *     enabled: bool,
     *     minWordSizeForTypos: array{oneTypo: int, twoTypos: int},
     *     disableOnWords: list<non-empty-string>,
     *     disableOnAttributes: list<non-empty-string>,
     *     disableOnNumbers: bool
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
     *     disableOnAttributes: list<non-empty-string>,
     *     disableOnNumbers: bool
     * } $typoTolerance
     */
    public function updateTypoTolerance(array $typoTolerance): Task
    {
        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid.'/settings/typo-tolerance', new TypoTolerance($typoTolerance)), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetTypoTolerance(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/typo-tolerance'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateDictionary(array $wordDictionary): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/dictionary', $wordDictionary), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetDictionary(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/dictionary'), partial(Tasks::waitTask(...), $this->http));
    }

    // Settings - Separator tokens

    public function getSeparatorTokens(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/separator-tokens');
    }

    /**
     * @param list<non-empty-string> $separatorTokens
     */
    public function updateSeparatorTokens(array $separatorTokens): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/separator-tokens', $separatorTokens), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetSeparatorTokens(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/separator-tokens'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateNonSeparatorTokens(array $nonSeparatorTokens): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/non-separator-tokens', $nonSeparatorTokens), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetNonSeparatorTokens(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/non-separator-tokens'), partial(Tasks::waitTask(...), $this->http));
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
    public function updateProximityPrecision(string $type): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/proximity-precision', $type), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetProximityPrecision(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/proximity-precision'), partial(Tasks::waitTask(...), $this->http));
    }

    // Settings - searchCutoffMs

    /**
     * @return non-negative-int|null
     */
    public function getSearchCutoffMs(): ?int
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/search-cutoff-ms');
    }

    /**
     * @param non-negative-int $value
     */
    public function updateSearchCutoffMs(int $value): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/search-cutoff-ms', $value), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetSearchCutoffMs(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/search-cutoff-ms'), partial(Tasks::waitTask(...), $this->http));
    }

    // Settings - Embedders

    /**
     * @since Meilisearch v1.13.0
     */
    public function getEmbedders(): ?array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/embedders');
    }

    /**
     * @since Meilisearch v1.13.0
     */
    public function updateEmbedders(array $embedders): Task
    {
        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid.'/settings/embedders', $embedders), partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * @since Meilisearch v1.13.0
     */
    public function resetEmbedders(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/embedders'), partial(Tasks::waitTask(...), $this->http));
    }

    // Settings - Facet Search

    /**
     * @since Meilisearch v1.12.0
     */
    public function getFacetSearch(): bool
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/facet-search');
    }

    /**
     * @since Meilisearch v1.12.0
     */
    public function updateFacetSearch(bool $facetSearch): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/facet-search', $facetSearch), partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * @since Meilisearch v1.12.0
     */
    public function resetFacetSearch(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/facet-search'), partial(Tasks::waitTask(...), $this->http));
    }

    // Settings - Prefix Search

    /**
     * @return 'indexingTime'|'disabled'
     *
     * @since Meilisearch v1.12.0
     */
    public function getPrefixSearch(): string
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/prefix-search');
    }

    /**
     * @param 'indexingTime'|'disabled' $prefixSearch
     *
     * @since Meilisearch v1.12.0
     */
    public function updatePrefixSearch(string $prefixSearch): Task
    {
        return Task::fromArray($this->http->put(self::PATH.'/'.$this->uid.'/settings/prefix-search', $prefixSearch), partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * @since Meilisearch v1.12.0
     */
    public function resetPrefixSearch(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings/prefix-search'), partial(Tasks::waitTask(...), $this->http));
    }

    // Settings - Chat

    /**
     * @since Meilisearch v1.15.1
     *
     * @return array{
     *     description: string,
     *     documentTemplate: string,
     *     documentTemplateMaxBytes: int,
     *     searchParameters: array{
     *         indexUid?: non-empty-string,
     *         q?: string,
     *         filter?: list<non-empty-string|list<non-empty-string>>,
     *         locales?: list<non-empty-string>,
     *         attributesToRetrieve?: list<non-empty-string>,
     *         attributesToCrop?: list<non-empty-string>,
     *         cropLength?: positive-int,
     *         attributesToHighlight?: list<non-empty-string>,
     *         cropMarker?: string,
     *         highlightPreTag?: string,
     *         highlightPostTag?: string,
     *         facets?: list<non-empty-string>,
     *         showMatchesPosition?: bool,
     *         sort?: list<non-empty-string>,
     *         matchingStrategy?: 'last'|'all'|'frequency',
     *         offset?: non-negative-int,
     *         limit?: non-negative-int,
     *         hitsPerPage?: non-negative-int,
     *         page?: non-negative-int,
     *         vector?: non-empty-list<float|non-empty-list<float>>,
     *         hybrid?: array<mixed>,
     *         attributesToSearchOn?: non-empty-list<non-empty-string>,
     *         showRankingScore?: bool,
     *         showRankingScoreDetails?: bool,
     *         rankingScoreThreshold?: float,
     *         distinct?: non-empty-string,
     *         federationOptions?: array<mixed>
     *     }
     * }
     */
    public function getChat(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings/chat');
    }

    /**
     * @since Meilisearch v1.15.1
     *
     * @param array{
     *     description: string,
     *     documentTemplate: string,
     *     documentTemplateMaxBytes: int,
     *     searchParameters: array{
     *         indexUid?: non-empty-string,
     *         q?: string,
     *         filter?: list<non-empty-string|list<non-empty-string>>,
     *         locales?: list<non-empty-string>,
     *         attributesToRetrieve?: list<non-empty-string>,
     *         attributesToCrop?: list<non-empty-string>,
     *         cropLength?: positive-int,
     *         attributesToHighlight?: list<non-empty-string>,
     *         cropMarker?: string,
     *         highlightPreTag?: string,
     *         highlightPostTag?: string,
     *         facets?: list<non-empty-string>,
     *         showMatchesPosition?: bool,
     *         sort?: list<non-empty-string>,
     *         matchingStrategy?: 'last'|'all'|'frequency',
     *         offset?: non-negative-int,
     *         limit?: non-negative-int,
     *         hitsPerPage?: non-negative-int,
     *         page?: non-negative-int,
     *         vector?: non-empty-list<float|non-empty-list<float>>,
     *         hybrid?: array<mixed>,
     *         attributesToSearchOn?: non-empty-list<non-empty-string>,
     *         showRankingScore?: bool,
     *         showRankingScoreDetails?: bool,
     *         rankingScoreThreshold?: float,
     *         distinct?: non-empty-string,
     *         federationOptions?: array<mixed>
     *     }
     * } $chatSettings
     */
    public function updateChat(array $chatSettings): Task
    {
        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid.'/settings/chat', $chatSettings));
    }
}
