<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\FacetSearchQuery;
use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\Index\Settings;
use Meilisearch\Contracts\IndexesQuery;
use Meilisearch\Contracts\IndexesResults;
use Meilisearch\Contracts\IndexStats;
use Meilisearch\Contracts\SimilarDocumentsQuery;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\TasksResults;
use Meilisearch\Endpoints\Delegates\HandlesDocuments;
use Meilisearch\Endpoints\Delegates\HandlesSettings;
use Meilisearch\Endpoints\Delegates\HandlesTasks;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Search\FacetSearchResult;
use Meilisearch\Search\SearchResult;
use Meilisearch\Search\SimilarDocumentsSearchResult;

use function Meilisearch\partial;

/**
 * @phpstan-import-type RawIndexStats from IndexStats
 * @phpstan-import-type SettingsArray from Settings
 * @phpstan-import-type RawTasks from Tasks
 * @phpstan-import-type TasksResponse from Tasks
 *
 * @phpstan-type RawIndex array{
 *     uid: non-empty-string,
 *     primaryKey: non-empty-string|null,
 *     createdAt: non-empty-string|null,
 *     updatedAt: non-empty-string|null
 * }
 * @phpstan-type RawSearchResult array{
 *     hits: array<int, array<string, mixed>>,
 *     processingTimeMs: non-negative-int,
 *     query: string,
 *     facetDistribution?: array<string, mixed>,
 *     facetStats?: array<string, mixed>,
 *     offset?: non-negative-int,
 *     limit?: non-negative-int,
 *     semanticHitCount?: non-negative-int,
 *     page?: non-negative-int,
 *     totalPages?: non-negative-int,
 *     totalHits?: non-negative-int,
 *     estimatedTotalHits?: non-negative-int,
 *     hitsPerPage?: non-negative-int
 * }
 * @phpstan-type RawSearchResultWithNbHits array{
 *     hits: array<int, array<string, mixed>>,
 *     processingTimeMs: non-negative-int,
 *     query: string,
 *     facetDistribution?: array<string, mixed>,
 *     facetStats?: array<string, mixed>,
 *     offset?: non-negative-int,
 *     limit?: non-negative-int,
 *     semanticHitCount?: non-negative-int,
 *     page?: non-negative-int,
 *     totalPages?: non-negative-int,
 *     totalHits?: non-negative-int,
 *     estimatedTotalHits?: non-negative-int,
 *     hitsPerPage?: non-negative-int,
 *     nbHits?: non-negative-int
 * }
 * @phpstan-type IndexSearchParameters array{
 *     q?: string,
 *     filter?: non-empty-string|list<non-empty-string|list<non-empty-string>>,
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
 *     media?: array<string, mixed>
 * }
 * @phpstan-type SearchResultOptions array{
 *     transformHits?: callable(array<int, array<string, mixed>>): array<int, array<string, mixed>>,
 *     transformFacetDistribution?: callable(array<string, mixed>): array<string, mixed>
 * }
 */
class Indexes extends Endpoint
{
    use HandlesDocuments;
    use HandlesSettings;
    use HandlesTasks;

    protected const PATH = '/indexes';

    private ?string $uid;
    private ?string $primaryKey;
    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;

    public function __construct(Http $http, ?string $uid = null, ?string $primaryKey = null, ?\DateTimeInterface $createdAt = null, ?\DateTimeInterface $updatedAt = null)
    {
        $this->uid = $uid;
        $this->primaryKey = $primaryKey;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->tasks = new Tasks($http);

        parent::__construct($http);
    }

    /**
     * @param RawIndex $attributes
     */
    protected function newInstance(array $attributes): self
    {
        return new self(
            $this->http,
            $attributes['uid'],
            $attributes['primaryKey'],
            null !== $attributes['createdAt'] ? new \DateTimeImmutable($attributes['createdAt']) : null,
            null !== $attributes['updatedAt'] ? new \DateTimeImmutable($attributes['updatedAt']) : null,
        );
    }

    /**
     * @param RawIndex $attributes
     *
     * @return $this
     */
    protected function fill(array $attributes): self
    {
        $this->uid = $attributes['uid'];
        $this->primaryKey = $attributes['primaryKey'];
        $this->createdAt = null !== $attributes['createdAt'] ? new \DateTimeImmutable($attributes['createdAt']) : null;
        $this->updatedAt = null !== $attributes['updatedAt'] ? new \DateTimeImmutable($attributes['updatedAt']) : null;

        return $this;
    }

    /**
     * @param array{primaryKey?: non-empty-string} $options
     *
     * @throws \Exception|ApiException
     */
    public function create(string $uid, array $options = []): Task
    {
        $options['uid'] = $uid;

        return Task::fromArray($this->http->post(self::PATH, $options), partial(Tasks::waitTask(...), $this->http));
    }

    public function all(?IndexesQuery $options = null): IndexesResults
    {
        $indexes = [];
        $query = isset($options) ? $options->toArray() : [];
        $response = $this->allRaw($query);

        foreach ($response['results'] as $index) {
            /** @var RawIndex $rawIndex */
            $rawIndex = $index;
            $indexes[] = $this->newInstance($rawIndex);
        }

        $response['results'] = $indexes;

        return new IndexesResults($response);
    }

    public function allRaw(array $options = []): array
    {
        return $this->http->get(self::PATH, $options);
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    public function fetchPrimaryKey(): ?string
    {
        return $this->fetchInfo()->getPrimaryKey();
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function fetchRawInfo(): ?array
    {
        return $this->http->get(self::PATH.'/'.$this->uid);
    }

    public function fetchInfo(): self
    {
        $response = $this->fetchRawInfo();
        \assert(null !== $response);
        /** @var RawIndex $typedResponse */
        $typedResponse = $response;

        return $this->fill($typedResponse);
    }

    /**
     * @param array{primaryKey?: non-empty-string} $body
     */
    public function update(array $body): Task
    {
        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid, $body), partial(Tasks::waitTask(...), $this->http));
    }

    public function delete(): Task
    {
        $response = $this->http->delete(self::PATH.'/'.$this->uid);
        \assert(null !== $response);

        return Task::fromArray($response, partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * @param array<array{indexes: mixed}> $indexes
     */
    public function swapIndexes(array $indexes): Task
    {
        return Task::fromArray($this->http->post('/swap-indexes', $indexes), partial(Tasks::waitTask(...), $this->http));
    }

    public function compact(): Task
    {
        return Task::fromArray($this->http->post(self::PATH.'/'.$this->uid.'/compact'), partial(Tasks::waitTask(...), $this->http));
    }

    // Tasks

    public function getTask(int $uid): Task
    {
        return Task::fromArray($this->http->get('/tasks/'.$uid), partial(Tasks::waitTask(...), $this->http));
    }

    public function getTasks(?TasksQuery $options = null): TasksResults
    {
        $options = $options ?? new TasksQuery();

        if ([] !== $options->getIndexUids()) {
            $options->setIndexUids([$this->uid, ...$options->getIndexUids()]);
        } else {
            $options->setIndexUids([$this->uid]);
        }

        $rawResponse = $this->http->get('/tasks', $options->toArray());
        /** @var RawTasks $response */
        $response = $rawResponse;
        $results = array_map(fn (array $task): Task => Task::fromArray($task, partial(Tasks::waitTask(...), $this->http)), $response['results']);
        /** @var TasksResponse $tasksResponse */
        $tasksResponse = [
            'results' => $results,
            'from' => $response['from'],
            'limit' => $response['limit'],
            'next' => $response['next'],
            'total' => $response['total'],
        ];

        return new TasksResults($tasksResponse);
    }

    // Search

    /**
     * @phpstan-param IndexSearchParameters $searchParams
     *
     * @phpstan-return ($options is array{raw: true|non-falsy-string|positive-int, ...} ? array : SearchResult)
     */
    public function search(?string $query, array $searchParams = [], array $options = []): SearchResult|array
    {
        $result = $this->rawSearch($query, $searchParams);
        /** @var RawSearchResult $rawResult */
        $rawResult = $result;

        if (\array_key_exists('raw', $options) && $options['raw']) {
            return $rawResult;
        }

        $searchResultOptions = $options;
        unset($searchResultOptions['raw']);
        /** @var SearchResultOptions $typedSearchResultOptions */
        $typedSearchResultOptions = $searchResultOptions;
        $searchResult = new SearchResult($rawResult);
        $searchResult->applyOptions($typedSearchResultOptions);

        return $searchResult;
    }

    /**
     * @phpstan-param IndexSearchParameters $searchParams
     *
     * @phpstan-return RawSearchResultWithNbHits
     */
    public function rawSearch(?string $query, array $searchParams = []): array
    {
        $parameters = array_merge(
            ['q' => $query],
            $searchParams
        );

        $result = $this->http->post(self::PATH.'/'.$this->uid.'/search', $parameters);

        // patch to prevent breaking in laravel/scout getTotalCount method,
        // affects only Meilisearch >= v0.28.0.
        if (isset($result['estimatedTotalHits'])) {
            $result['nbHits'] = $result['estimatedTotalHits'];
        }

        /** @var RawSearchResultWithNbHits $typedResult */
        $typedResult = $result;

        return $typedResult;
    }

    public function searchSimilarDocuments(SimilarDocumentsQuery $parameters): SimilarDocumentsSearchResult
    {
        $result = $this->http->post(self::PATH.'/'.$this->uid.'/similar', $parameters->toArray());

        return new SimilarDocumentsSearchResult($result);
    }

    // Facet Search

    public function facetSearch(FacetSearchQuery $params): FacetSearchResult
    {
        $response = $this->http->post(self::PATH.'/'.$this->uid.'/facet-search', $params->toArray());

        return new FacetSearchResult($response);
    }

    // Stats

    public function stats(): IndexStats
    {
        /** @var RawIndexStats $raw */
        $raw = $this->http->get(self::PATH.'/'.$this->uid.'/stats');

        return IndexStats::fromArray($raw);
    }

    // Settings - Global

    public function getSettings(): Settings
    {
        return new Settings($this->http->get(self::PATH.'/'.$this->uid.'/settings'));
    }

    /**
     * @param SettingsArray|Settings $settings
     */
    public function updateSettings(array|Settings $settings): Task
    {
        $body = $settings instanceof Settings ? $settings->toArray() : $settings;

        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid.'/settings', $body), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetSettings(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings'), partial(Tasks::waitTask(...), $this->http));
    }
}
