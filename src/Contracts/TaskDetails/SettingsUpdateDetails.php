<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     dictionary?: list<string>,
 *     displayedAttributes?: list<string>,
 *     distinctAttribute?: string,
 *     embedders?: non-empty-array<non-empty-string, array{
 *         apiKey?: string,
 *         binaryQuantized?: bool,
 *         dimensions?: int,
 *         distribution?: array{mean: float, sigma: float},
 *         documentTemplate?: string,
 *         documentTemplateMaxBytes?: int,
 *         indexingEmbedder?: array{model: string, source: string},
 *         model?: string,
 *         pooling?: string,
 *         request?: array<string, mixed>,
 *         response?: array<string, mixed>,
 *         revision?: string,
 *         searchEmbedder?: array{model: string, source: string},
 *         source?: string,
 *         url?: string
 *     }>,
 *     faceting?: array{maxValuesPerFacet: non-negative-int, sortFacetValuesBy: array<string, 'alpha'|'count'>}|null,
 *     facetSearch?: bool,
 *     filterableAttributes?: list<string|array{attributePatterns: list<string>, features: array{facetSearch: bool, filter: array{equality: bool, comparison: bool}}}>|null,
 *     localizedAttributes?: list<array{locales: list<non-empty-string>, attributePatterns: list<string>}>,
 *     nonSeparatorTokens?: list<string>,
 *     pagination?: array{maxTotalHits: non-negative-int},
 *     prefixSearch?: non-empty-string|null,
 *     proximityPrecision?: 'byWord'|'byAttribute',
 *     rankingRules?: list<non-empty-string>,
 *     searchableAttributes?: list<non-empty-string>,
 *     searchCutoffMs?: non-negative-int,
 *     separatorTokens?: list<string>,
 *     sortableAttributes?: list<non-empty-string>,
 *     stopWords?: list<string>,
 *     synonyms?: array<string, list<string>>,
 *     typoTolerance?: array{
 *         enabled: bool,
 *         minWordSizeForTypos: array{oneTypo: int, twoTypos: int},
 *         disableOnWords: list<string>,
 *         disableOnAttributes: list<string>
 *     }
 * }>
 */
final class SettingsUpdateDetails implements TaskDetails
{
    /**
     * @param list<string>|null $dictionary
     * @param list<string>|null $displayedAttributes
     * @param non-empty-array<non-empty-string, array{
     *     apiKey?: string,
     *     binaryQuantized?: bool,
     *     dimensions?: int,
     *     distribution?: array{mean: float, sigma: float},
     *     documentTemplate?: string,
     *     documentTemplateMaxBytes?: int,
     *     indexingEmbedder?: array{model: string, source: string},
     *     model?: string,
     *     pooling?: string,
     *     request?: array<string, mixed>,
     *     response?: array<string, mixed>,
     *     revision?: string,
     *     searchEmbedder?: array{model: string, source: string},
     *     source?: string,
     *     url?: string
     * }>|null $embedders
     * @param array{maxValuesPerFacet: non-negative-int, sortFacetValuesBy: array<string, 'alpha'|'count'>}|null                                            $faceting
     * @param list<string|array{attributePatterns: list<string>, features: array{facetSearch: bool, filter: array{equality: bool, comparison: bool}}}>|null $filterableAttributes
     * @param list<array{locales: list<non-empty-string>, attributePatterns: list<string>}>|null                                                            $localizedAttributes
     * @param list<string>|null                                                                                                                             $nonSeparatorTokens
     * @param array{maxTotalHits: non-negative-int}|null                                                                                                    $pagination
     * @param 'indexingTime'|'disabled'|null                                                                                                                $prefixSearch
     * @param 'byWord'|'byAttribute'|null                                                                                                                   $proximityPrecision
     * @param list<non-empty-string>|null                                                                                                                   $rankingRules
     * @param list<non-empty-string>|null                                                                                                                   $searchableAttributes
     * @param non-negative-int|null                                                                                                                         $searchCutoffMs
     * @param list<string>                                                                                                                                  $separatorTokens
     * @param list<non-empty-string>|null                                                                                                                   $sortableAttributes
     * @param list<string>|null                                                                                                                             $stopWords
     * @param array<string, list<string>>|null                                                                                                              $synonyms
     * @param array{
     *     enabled: bool,
     *     minWordSizeForTypos: array{oneTypo: int, twoTypos: int},
     *     disableOnWords: list<string>,
     *     disableOnAttributes: list<string>
     * }|null $typoTolerance
     */
    public function __construct(
        public readonly ?array $dictionary,
        public readonly ?array $displayedAttributes,
        public readonly ?string $distinctAttribute,
        public readonly ?array $embedders,
        public readonly ?array $faceting,
        public readonly ?bool $facetSearch,
        public readonly ?array $filterableAttributes,
        public readonly ?array $localizedAttributes,
        public readonly ?array $nonSeparatorTokens,
        public readonly ?array $pagination,
        public readonly ?string $prefixSearch,
        public readonly ?string $proximityPrecision,
        public readonly ?array $rankingRules,
        public readonly ?array $searchableAttributes,
        public readonly ?int $searchCutoffMs,
        public readonly ?array $separatorTokens,
        public readonly ?array $sortableAttributes,
        public readonly ?array $stopWords,
        public readonly ?array $synonyms,
        public readonly ?array $typoTolerance,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['dictionary'] ?? null,
            $data['displayedAttributes'] ?? null,
            $data['distinctAttribute'] ?? null,
            $data['embedders'] ?? null,
            $data['faceting'] ?? null,
            $data['facetSearch'] ?? null,
            $data['filterableAttributes'] ?? null,
            $data['localizedAttributes'] ?? null,
            $data['nonSeparatorTokens'] ?? null,
            $data['pagination'] ?? null,
            $data['prefixSearch'] ?? null,
            $data['proximityPrecision'] ?? null,
            $data['rankingRules'] ?? null,
            $data['searchableAttributes'] ?? null,
            $data['searchCutoffMs'] ?? null,
            $data['separatorTokens'] ?? null,
            $data['sortableAttributes'] ?? null,
            $data['stopWords'] ?? null,
            $data['synonyms'] ?? null,
            $data['typoTolerance'] ?? null,
        );
    }
}
