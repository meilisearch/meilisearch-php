<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\FacetSearchQuery;
use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\Index\Settings;
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

/**
 * @phpstan-import-type SearchQueryArray from SearchQuery
 */
final class Index extends Endpoint
{
    use HandlesDocuments;
    use HandlesSettings;
    use HandlesTasks;

    protected const PATH = '/indexes';

    protected string $uid;
    protected ?string $primaryKey;
    protected \DateTimeInterface $createdAt;
    protected \DateTimeInterface $updatedAt;

    public function __construct(Http $http, string $uid, ?string $apiKey = null)
    {
        parent::__construct($http, $apiKey);

        $this->uid = $uid;
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

    public function getCreatedAt(): \DateTimeInterface
    {
        if (!isset($this->createdAt)) {
            $this->load();
        }

        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        if (!isset($this->updatedAt)) {
            $this->load();
        }

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
        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid, $body), partial(Tasks::waitTask(...), $this->http));
    }

    public function delete(): Task
    {
        $response = $this->http->delete(self::PATH.'/'.$this->uid);
        \assert(null !== $response);

        return Task::fromArray($response, partial(Tasks::waitTask(...), $this->http));
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

        $response = $this->http->get('/tasks', $options->toArray());
        $response['results'] = array_map(fn (array $task) => Task::fromArray($task, partial(Tasks::waitTask(...), $this->http)), $response['results']);

        return new TasksResults($response);
    }

    /**
     * @param string|SearchQuery|null           $query
     * @param SearchQuery|SearchQueryArray|array{raw?: bool, transformFacetDistribution?: callable}|null $searchQuery
     *
     * @phpstan-return SearchResult|array
     */
    public function search(string|SearchQuery|null $query, SearchQuery|array|null $searchQuery = null, array $options = []): SearchResult|array
    {
        if ($query instanceof SearchQuery) {
            $resolvedQuery = $query;
            $options = \is_array($searchQuery) ? $searchQuery : $options;
        } else {
            $resolvedQuery = $this->resolveSearchQuery($query, $searchQuery);
        }

        $result = $this->rawSearch($resolvedQuery);

        if (\array_key_exists('raw', $options) && $options['raw']) {
            return $result;
        }

        $searchResult = new SearchResult($result);
        $searchResult->applyOptions($options);

        return $searchResult;
    }

    /**
     * @param string|SearchQuery|null           $query
     * @param SearchQuery|SearchQueryArray|null $searchQuery
     */
    public function rawSearch(string|SearchQuery|null $query, SearchQuery|array|null $searchQuery = null): array
    {
        $resolvedQuery = $this->resolveSearchQuery($query, $searchQuery);

        $result = $this->http->post(self::PATH.'/'.$this->uid.'/search', (object) $resolvedQuery->toArray());

        // patch to prevent breaking in laravel/scout getTotalCount method,
        // affects only Meilisearch >= v0.28.0.
        if (isset($result['estimatedTotalHits'])) {
            $result['nbHits'] = $result['estimatedTotalHits'];
        }

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

    public function stats(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/stats');
    }

    // Settings - Global

    public function getSettings(): array
    {
        return (new Settings($this->http->get(self::PATH.'/'.$this->uid.'/settings')))
            ->getIterator()->getArrayCopy();
    }

    public function updateSettings($settings): Task
    {
        return Task::fromArray($this->http->patch(self::PATH.'/'.$this->uid.'/settings', $settings), partial(Tasks::waitTask(...), $this->http));
    }

    public function resetSettings(): Task
    {
        return Task::fromArray($this->http->delete(self::PATH.'/'.$this->uid.'/settings'), partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * @param string|SearchQuery|null           $query
     * @param SearchQuery|SearchQueryArray|null $searchQuery
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
