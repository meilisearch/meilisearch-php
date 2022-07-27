<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use DateTime;
use Exception;
use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Contracts\Index\Settings;
use MeiliSearch\Contracts\IndexesQuery;
use MeiliSearch\Contracts\IndexesResults;
use MeiliSearch\Contracts\TasksQuery;
use MeiliSearch\Contracts\TasksResults;
use MeiliSearch\Endpoints\Delegates\HandlesDocuments;
use MeiliSearch\Endpoints\Delegates\HandlesSettings;
use MeiliSearch\Endpoints\Delegates\HandlesTasks;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Search\SearchResult;

class Indexes extends Endpoint
{
    use HandlesDocuments;
    use HandlesSettings;
    use HandlesTasks;

    protected const PATH = '/indexes';

    private ?string $uid;
    private ?string $primaryKey;
    private ?string $createdAt;
    private ?string $updatedAt;
    private Tasks $tasks;

    public function __construct(Http $http, $uid = null, $primaryKey = null, $createdAt = null, $updatedAt = null)
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
            $attributes['createdAt'],
            $attributes['updatedAt'],
        );
    }

    /**
     * @return $this
     */
    protected function fill(array $attributes): self
    {
        $this->uid = $attributes['uid'];
        $this->primaryKey = $attributes['primaryKey'];
        $this->createdAt = $attributes['createdAt'];
        $this->updatedAt = $attributes['updatedAt'];

        return $this;
    }

    /**
     * @throws Exception|ApiException
     */
    public function create(string $uid, array $options = []): array
    {
        $options['uid'] = $uid;

        return $this->http->post(self::PATH, $options);
    }

    public function all(IndexesQuery $options = null): IndexesResults
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

    public function getCreatedAt(): ?DateTime
    {
        return static::parseDate($this->createdAt);
    }

    public function getCreatedAtString(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return static::parseDate($this->updatedAt);
    }

    public function getUpdatedAtString(): ?string
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

    // Tasks

    public function getTask($uid): array
    {
        return $this->http->get('/tasks/'.$uid);
    }

    public function getTasks(TasksQuery $options = null): TasksResults
    {
        $options = $options ?? new TasksQuery();

        if (0 == \count($options->getUid())) {
            $options->setUid(array_merge([$this->uid], $options->getUid()));
        } else {
            $options->setUid([$this->uid]);
        }

        $response = $this->http->get('/tasks', $options->toArray());

        return new TasksResults($response);
    }

    // Search

    /**
     * @return SearchResult|array
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
     * @throws Exception
     */
    public static function parseDate(?string $dateTime): ?DateTime
    {
        if (null === $dateTime) {
            return null;
        }

        try {
            return new DateTime($dateTime);
        } catch (\Exception $e) {
            // Trim 9th+ digit from fractional seconds. Meilisearch server can send 9 digits; PHP supports up to 8
            $trimPattern = '/(^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{1,8})(?:\d{1,})?(Z|[\+-]\d{2}:\d{2})$/';
            $trimmedDate = preg_replace($trimPattern, '$1$2', $dateTime);

            return new DateTime($trimmedDate);
        }
    }
}
