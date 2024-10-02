<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\FacetSearchQuery;
use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\Index\Settings;
use Meilisearch\Contracts\IndexesQuery;
use Meilisearch\Contracts\IndexesResults;
use Meilisearch\Contracts\SimilarDocumentsQuery;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Contracts\TasksResults;
use Meilisearch\Endpoints\Delegates\HandlesDocuments;
use Meilisearch\Endpoints\Delegates\HandlesSettings;
use Meilisearch\Endpoints\Delegates\HandlesTasks;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Search\FacetSearchResult;
use Meilisearch\Search\SearchResult;
use Meilisearch\Search\SimilarDocumentsSearchResult;

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

    protected function newInstance(array $attributes): self
    {
        return new self(
            $this->http,
            $attributes['uid'],
            $attributes['primaryKey'],
            static::parseDate($attributes['createdAt']),
            static::parseDate($attributes['updatedAt']),
        );
    }

    /**
     * @return $this
     */
    protected function fill(array $attributes): self
    {
        $this->uid = $attributes['uid'];
        $this->primaryKey = $attributes['primaryKey'];
        $this->createdAt = static::parseDate($attributes['createdAt']);
        $this->updatedAt = static::parseDate($attributes['updatedAt']);

        return $this;
    }

    /**
     * @throws \Exception|ApiException
     */
    public function create(string $uid, array $options = []): array
    {
        $options['uid'] = $uid;

        return $this->http->post(self::PATH, $options);
    }

    public function all(?IndexesQuery $options = null): IndexesResults
    {
        $indexes = [];
        $query = isset($options) ? $options->toArray() : [];
        $response = $this->allRaw($query);

        foreach ($response['results'] as $index) {
            $indexes[] = $this->newInstance($index);
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

        return $this->fill($response);
    }

    public function update($body): array
    {
        return $this->http->patch(self::PATH.'/'.$this->uid, $body);
    }

    public function delete(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid) ?? [];
    }

    /**
     * @param array<array{indexes: mixed}> $indexes
     */
    public function swapIndexes(array $indexes): array
    {
        return $this->http->post('/swap-indexes', $indexes);
    }

    // Tasks

    public function getTask($uid): array
    {
        return $this->http->get('/tasks/'.$uid);
    }

    public function getTasks(?TasksQuery $options = null): TasksResults
    {
        $options = $options ?? new TasksQuery();

        if (\count($options->getIndexUids()) > 0) {
            $options->setIndexUids(array_merge([$this->uid], $options->getIndexUids()));
        } else {
            $options->setIndexUids([$this->uid]);
        }

        $response = $this->http->get('/tasks', $options->toArray());

        return new TasksResults($response);
    }

    // Search

    /**
     * @return SearchResult|array
     *
     * @phpstan-return ($options is array{raw: true|non-falsy-string|positive-int} ? array : SearchResult)
     */
    public function search(?string $query, array $searchParams = [], array $options = [])
    {
        $result = $this->rawSearch($query, $searchParams);

        if (\array_key_exists('raw', $options) && $options['raw']) {
            return $result;
        }

        $searchResult = new SearchResult($result);
        $searchResult->applyOptions($options);

        return $searchResult;
    }

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

    public function updateSettings($settings): array
    {
        return $this->http->patch(self::PATH.'/'.$this->uid.'/settings', $settings);
    }

    public function resetSettings(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings');
    }

    /**
     * @throws \Exception
     */
    public static function parseDate(?string $dateTime): ?\DateTimeInterface
    {
        if (null === $dateTime) {
            return null;
        }

        try {
            return new \DateTimeImmutable($dateTime);
        } catch (\Exception $e) {
            // Trim 9th+ digit from fractional seconds. Meilisearch server can send 9 digits; PHP supports up to 8
            $trimPattern = '/(^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{1,8})(?:\d{1,})?(Z|[\+-]\d{2}:\d{2})$/';
            $trimmedDate = preg_replace($trimPattern, '$1$2', $dateTime);

            return new \DateTimeImmutable($trimmedDate);
        }
    }
}
