<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\FacetSearchQuery;
use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\Index\Settings;
use Meilisearch\Contracts\IndexStats;
use Meilisearch\Contracts\SearchQuery;
use Meilisearch\Contracts\SimilarDocumentsQuery;
use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\TasksResults;
use Meilisearch\Endpoints\Delegates\HandlesDocuments;
use Meilisearch\Endpoints\Delegates\HandlesSettings;
use Meilisearch\Endpoints\Delegates\HandlesTasks;
use Meilisearch\Search\FacetSearchResult;
use Meilisearch\Search\SearchResult;
use Meilisearch\Search\SimilarDocumentsSearchResult;

use function Meilisearch\partial;
use function Meilisearch\waitForTask;

/**
 * @phpstan-import-type RawIndexStats from IndexStats
 * @phpstan-import-type RawSearchQuery from SearchQuery
 * @phpstan-import-type RawSettings from Settings
 *
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
 * @phpstan-type SearchResultOptions array{
 *     transformHits?: callable(array<int, array<string, mixed>>): array<int, array<string, mixed>>,
 *     transformFacetDistribution?: callable(array<string, mixed>): array<string, mixed>
 * }
 */
final class Index extends Endpoint
{
    use HandlesDocuments;
    use HandlesSettings;
    use HandlesTasks;

    protected const PATH = '/indexes';

    protected string $uid;
    protected ?string $primaryKey = null;
    protected ?\DateTimeInterface $createdAt = null;
    protected ?\DateTimeInterface $updatedAt = null;

    public function __construct(Http $http, string $uid)
    {
        parent::__construct($http);

        $this->uid = $uid;
        $this->tasks = new Tasks($http);
    }

    /**
     * @param array{
     *     uid: non-empty-string,
     *     primaryKey: string|null,
     *     createdAt: non-empty-string,
     *     updatedAt: non-empty-string
     * } $data
     */
    public static function fromArray(array $data, Http $http): self
    {
        $index = new self($http, $data['uid']);
        $index->primaryKey = $data['primaryKey'];
        $index->createdAt = new \DateTimeImmutable($data['createdAt']);
        $index->updatedAt = new \DateTimeImmutable($data['updatedAt']);

        return $index;
    }

    public function getPrimaryKey(): ?string
    {
        if (!isset($this->createdAt)) {
            $this->load();
        }

        return $this->primaryKey;
    }

    /**
     * @return non-empty-string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * Lazily loads the index metadata on first call.
     *
     * @return array{
     *     uid: non-empty-string,
     *     primaryKey: string|null,
     *     createdAt: non-empty-string,
     *     updatedAt: non-empty-string
     * }
     *
     * @throws \Meilisearch\Exceptions\ApiException if the index cannot be fetched
     */
    public function toArray(): array
    {
        return [
            'uid' => $this->getUid(),
            'primaryKey' => $this->getPrimaryKey(),
            'createdAt' => \Meilisearch\formatRfc3339Micro($this->getCreatedAt()),
            'updatedAt' => \Meilisearch\formatRfc3339Micro($this->getUpdatedAt()),
        ];
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        if (!isset($this->createdAt)) {
            $this->load();
        }

        \assert($this->createdAt instanceof \DateTimeInterface);

        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        if (!isset($this->updatedAt)) {
            $this->load();
        }

        \assert($this->updatedAt instanceof \DateTimeInterface);

        return $this->updatedAt;
    }

    /**
     * @return array{
     *     uid: non-empty-string,
     *     primaryKey: string|null,
     *     createdAt: non-empty-string,
     *     updatedAt: non-empty-string
     * }
     */
    public function fetchRawInfo(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid);
    }

    /**
     * @param array{
     *     primaryKey?: string|null,
     *     uid?: non-empty-string
     * } $body
     */
    public function update(array $body): Task
    {
        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid, $body), partial(waitForTask(...), $this->http));
    }

    public function delete(): Task
    {
        $response = $this->http->delete(self::PATH.'/'.$this->uid);
        \assert(null !== $response);

        return Task::fromArray($response, partial(waitForTask(...), $this->http));
    }

    public function compact(): Task
    {
        return Task::fromArray($this->http->post(self::PATH.'/'.$this->uid.'/compact'), partial(waitForTask(...), $this->http));
    }

    // Tasks

    public function getTasks(?TasksQuery $options = null): TasksResults
    {
        $options = clone ($options ?? new TasksQuery());
        $options->setIndexUids([$this->uid, ...$options->getIndexUids()]);

        return $this->tasks->all($options);
    }

    /**
     * When $query is a SearchQuery instance, an array $searchParams is treated as SearchResultOptions.
     *
     * @param SearchQuery|RawSearchQuery|SearchResultOptions|null $searchParams
     * @param SearchResultOptions                                 $options      ignored when $query is a SearchQuery and $searchParams is an array
     */
    public function search(string|SearchQuery|null $query, SearchQuery|array|null $searchParams = null, array $options = []): SearchResult
    {
        if ($query instanceof SearchQuery) {
            $resolvedQuery = $query;
            $options = \is_array($searchParams) ? $searchParams : $options;
        } else {
            $resolvedQuery = $this->resolveSearchQuery($query, $searchParams);
        }

        /** @var RawSearchResult $rawResult */
        $rawResult = $this->rawSearch($resolvedQuery);
        $searchResult = new SearchResult($rawResult);

        return $searchResult->applyOptions($options);
    }

    /**
     * @param SearchQuery|RawSearchQuery|null $searchParams
     *
     * @return RawSearchResult
     */
    public function rawSearch(string|SearchQuery|null $query, SearchQuery|array|null $searchParams = null): array
    {
        $resolvedQuery = $this->resolveSearchQuery($query, $searchParams);

        /** @var RawSearchResult $result */
        $result = $this->http->post(self::PATH.'/'.$this->uid.'/search', (object) $resolvedQuery->toArray());

        return $result;
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
        /** @var RawSettings $raw */
        $raw = $this->http->get(self::PATH.'/'.$this->uid.'/settings');

        return new Settings($raw);
    }

    /**
     * @param RawSettings|Settings $settings
     */
    public function updateSettings(array|Settings $settings): Task
    {
        $body = $settings instanceof Settings ? $settings : new Settings($settings);

        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid.'/settings', $body), partial(waitForTask(...), $this->http));
    }

    public function resetSettings(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings'), partial(waitForTask(...), $this->http));
    }

    /**
     * @param SearchQuery|RawSearchQuery|null $searchQuery
     */
    private function resolveSearchQuery(string|SearchQuery|null $query, SearchQuery|array|null $searchQuery = null): SearchQuery
    {
        if ($query instanceof SearchQuery) {
            return $query;
        }

        if ($searchQuery instanceof SearchQuery) {
            return $searchQuery->setQuery($query);
        }

        return SearchQuery::fromArray(\is_array($searchQuery) ? $searchQuery : [])->setQuery($query);
    }

    private function load(): self
    {
        $response = $this->fetchRawInfo();

        $this->primaryKey = $response['primaryKey'];
        $this->createdAt = new \DateTimeImmutable($response['createdAt']);
        $this->updatedAt = new \DateTimeImmutable($response['updatedAt']);

        return $this;
    }
}
