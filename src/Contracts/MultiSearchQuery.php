<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type MultiSearchQueryArray array{
 *     indexUid?: non-empty-string,
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
 *     media?: array<mixed>,
 *     federationOptions?: array{weight?: float, remote?: non-empty-string}
 * }
 */
class MultiSearchQuery extends AbstractSearchQuery
{
    /**
     * @var non-empty-string|null
     */
    private ?string $indexUid = null;

    private ?FederationOptions $federationOptions = null;

    /**
     * @param MultiSearchQueryArray $data
     */
    public static function fromArray(array $data): self
    {
        $query = (new self())->hydrateFromArray($data);

        if (\array_key_exists('indexUid', $data)) {
            $query->setIndexUid($data['indexUid']);
        }
        if (\array_key_exists('federationOptions', $data)) {
            $query->setFederationOptions(FederationOptions::fromArray($data['federationOptions']));
        }

        return $query;
    }

    public function setIndexUid(string $uid): static
    {
        $this->indexUid = $uid;

        return $this;
    }

    /**
     * This option is only available while doing a federated search.
     * If used in another context an error will be returned by Meilisearch.
     */
    public function setFederationOptions(FederationOptions $federationOptions): static
    {
        $this->federationOptions = $federationOptions;

        return $this;
    }

    /**
     * @return MultiSearchQueryArray
     */
    public function toArray(): array
    {
        return array_filter(
            array_merge(
                ['indexUid' => $this->indexUid],
                $this->baseArray(),
                ['federationOptions' => null !== $this->federationOptions ? $this->federationOptions->toArray() : null]
            ),
            static function ($item) { return null !== $item; }
        );
    }
}
