<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use Exception;
use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Endpoints\Delegates\HandlesDocuments;
use MeiliSearch\Endpoints\Delegates\HandlesSettings;
use MeiliSearch\Exceptions\ApiException;
use MeiliSearch\Exceptions\TimeOutException;
use MeiliSearch\Search\SearchResult;

class Indexes extends Endpoint
{
    use HandlesDocuments;
    use HandlesSettings;

    protected const PATH = '/indexes';

    /**
     * @var string|null
     */
    private $uid;
    private $primaryKey;

    public function __construct(Http $http, $uid = null, $primaryKey = null)
    {
        $this->uid = $uid;
        $this->primaryKey = $primaryKey;
        parent::__construct($http);
    }

    /**
     * @return $this
     *
     * @throws Exception|ApiException
     */
    public function create(string $uid, array $options = []): self
    {
        $options['uid'] = $uid;

        $response = $this->http->post(self::PATH, $options);

        return new self($this->http, $response['uid'], $response['primaryKey']);
    }

    public function all(): array
    {
        $indexes = [];

        foreach ($this->http->get(self::PATH) as $index) {
            $indexes[] = new self($this->http, $index['uid']);
        }

        return $indexes;
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

    public function fetchRawInfo(): ?array
    {
        return $this->http->get(self::PATH.'/'.$this->uid);
    }

    public function fetchInfo(): self
    {
        $response = $this->fetchRawInfo();
        $this->uid = $response['uid'];
        $this->primaryKey = $response['primaryKey'];

        return $this;
    }

    public function update($body): self
    {
        $response = $this->http->put(self::PATH.'/'.$this->uid, $body);
        $this->uid = $response['uid'];
        $this->primaryKey = $response['primaryKey'];

        return $this;
    }

    public function delete(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid) ?? [];
    }

    // Updates

    public function getUpdateStatus($updateId): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/updates/'.$updateId);
    }

    public function getAllUpdateStatus(): array
    {
        return $this->http->get(self::PATH.'/'.$this->uid.'/updates');
    }

    /**
     * @param string $updateId
     * @param int    $timeoutInMs
     * @param int    $intervalInMs
     *
     * @throws TimeOutException
     */
    public function waitForPendingUpdate($updateId, $timeoutInMs = 5000, $intervalInMs = 50): array
    {
        $timeout_temp = 0;
        while ($timeoutInMs > $timeout_temp) {
            $res = $this->getUpdateStatus($updateId);
            if ('enqueued' != $res['status']) {
                return $res;
            }
            $timeout_temp += $intervalInMs;
            usleep(1000 * $intervalInMs);
        }
        throw new TimeOutException();
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
        return $this->http->post(self::PATH.'/'.$this->uid.'/settings', $settings);
    }

    public function resetSettings(): array
    {
        return $this->http->delete(self::PATH.'/'.$this->uid.'/settings');
    }
}
