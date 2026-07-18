<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\Index;

use Meilisearch\Contracts\Data;

/**
 * @phpstan-type SettingsEmbedders array<non-empty-string, array{
 *     apiKey?: string,
 *     binaryQuantized?: bool,
 *     dimensions?: int,
 *     distribution?: array{mean: float, sigma: float},
 *     documentTemplate?: string,
 *     documentTemplateMaxBytes?: int,
 *     indexingFragments?: array<string, mixed>,
 *     indexingEmbedder?: array{model: string, source: string},
 *     model?: string,
 *     pooling?: string,
 *     request?: array<string, mixed>,
 *     response?: array<string, mixed>,
 *     revision?: string,
 *     searchFragments?: array<string, mixed>,
 *     searchEmbedder?: array{model: string, source: string},
 *     source?: string,
 *     url?: string
 * }>
 * @phpstan-type SettingsTypoTolerance array{
 *     enabled?: bool,
 *     minWordSizeForTypos?: array{oneTypo?: int, twoTypos?: int},
 *     disableOnWords?: list<string>,
 *     disableOnAttributes?: list<string>,
 *     disableOnNumbers?: bool
 * }
 * @phpstan-type SettingsChatSearchParameters array{
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
 * @phpstan-type SettingsArray array{
 *     dictionary?: list<string>,
 *     displayedAttributes?: list<string>,
 *     distinctAttribute?: string|null,
 *     embedders?: SettingsEmbedders|null,
 *     faceting?: array{
 *         maxValuesPerFacet?: non-negative-int,
 *         sortFacetValuesBy?: array<non-empty-string, 'alpha'|'count'>
 *     }|null,
 *     facetSearch?: bool,
 *     filterableAttributes?: list<string|array{
 *         attributePatterns: list<string>,
 *         features?: array{
 *             facetSearch: bool,
 *             filter: array{equality: bool, comparison: bool}
 *         }
 *     }>|null,
 *     localizedAttributes?: list<array{locales: list<non-empty-string>, attributePatterns: list<string>}>,
 *     nonSeparatorTokens?: list<string>,
 *     pagination?: array{maxTotalHits: non-negative-int},
 *     prefixSearch?: 'indexingTime'|'disabled'|null,
 *     proximityPrecision?: 'byWord'|'byAttribute',
 *     rankingRules?: list<non-empty-string>,
 *     searchableAttributes?: list<non-empty-string>,
 *     searchCutoffMs?: non-negative-int|null,
 *     separatorTokens?: list<string>,
 *     sortableAttributes?: list<non-empty-string>,
 *     stopWords?: list<string>,
 *     synonyms?: array<string, list<string>>|null,
 *     typoTolerance?: SettingsTypoTolerance|null,
 *     chat?: array{
 *         description: string,
 *         documentTemplate: string,
 *         documentTemplateMaxBytes: int,
 *         searchParameters: SettingsChatSearchParameters
 *     }
 * }
 */
class Settings extends Data implements \JsonSerializable
{
    /**
     * @param SettingsArray $data
     */
    public function __construct(array $data = [])
    {
        $rawData = $data;

        if (\array_key_exists('synonyms', $rawData)) {
            $data['synonyms'] = null === $rawData['synonyms'] ? null : new Synonyms($rawData['synonyms']);
        }
        if (\array_key_exists('typoTolerance', $rawData)) {
            $data['typoTolerance'] = null === $rawData['typoTolerance'] ? null : new TypoTolerance($rawData['typoTolerance']);
        }
        if (\array_key_exists('faceting', $rawData)) {
            $data['faceting'] = null === $rawData['faceting'] ? null : new Faceting($rawData['faceting']);
        }
        if (\array_key_exists('embedders', $rawData)) {
            $data['embedders'] = null === $rawData['embedders'] ? null : new Embedders($rawData['embedders']);
        }

        parent::__construct($data);
    }

    public function jsonSerialize(): array
    {
        return $this->getIterator()->getArrayCopy();
    }

    /**
     * @return SettingsArray
     */
    public function toArray(): array
    {
        /** @var array<string, mixed> $settings */
        $settings = $this->getIterator()->getArrayCopy();

        foreach (['synonyms', 'typoTolerance', 'faceting', 'embedders'] as $key) {
            if (!\array_key_exists($key, $settings)) {
                continue;
            }

            $value = $settings[$key];
            if ($value instanceof Data) {
                $settings[$key] = $value->getIterator()->getArrayCopy();
            }
        }

        /** @var SettingsArray $typedSettings */
        $typedSettings = $settings;

        return $typedSettings;
    }
}
