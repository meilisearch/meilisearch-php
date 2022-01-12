<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use DateTime;
use Exception;
use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;
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

    /**
     * @var string|null
     */
    private $uid;

    /**
     * @var string|null
     */
    private $primaryKey;

    /**
     * @var string|null
     */
    private $createdAt;

    /**
     * @var string|null
     */
    private $updatedAt;

    /**
     * @var Tasks
     */
    private $tasks;

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
     * @return $this
     *
     * @throws Exception|ApiException
     */
    public function create(string $uid, array $options = []): array
    {
        $options['uid'] = $uid;

        return $this->http->post(self::PATH, $options);
    }

    public function all(): array
    {
        $indexes = [];

        foreach ($this->allRaw() as $index) {
            $indexes[] = $this->newInstance($index);
        }

        return $indexes;
    }

    public function allRaw(): array
    {
        return $this->http->get(self::PATH);
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
        return $this->http->put(self::PATH.'/'.$this->uid, $body);
    }

    public function delete(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid) ?? [];
    }

    // Tasks

    public function getTask($uid): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/tasks'.'/'.$uid);
    }

    public function getTasks(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/tasks');
    }

    // Search

    /**
     * @param string $query
     *
     * @return SearchResult|array
     */
    public function search($query, array $searchParams = [], array $options = [])
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

        return $this->http->post(self::PATH.'/'.$this->uid.'/search', $parameters);
    }

    // Stats

    public function stats(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/stats');
    }

    // Settings - Global

    public function getSettings(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/settings');
    }

    public function updateSettings($settings): array
    {
        // Patch related to https://github.com/meilisearch/meilisearch-php/issues/204
        // Should be removed when implementing https://github.com/meilisearch/meilisearch-php/issues/209
        if (\array_key_exists('synonyms', $settings) && 0 == \count($settings['synonyms'])) {
            $settings['synonyms'] = null;
        }

        return $this->http->post(self::PATH.'/'.$this->uid.'/settings', $settings);
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
