<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use Exception;
use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;
use MeiliSearch\Endpoints\Delegates\HandlesDocuments;
use MeiliSearch\Endpoints\Delegates\HandlesSettings;
use MeiliSearch\Exceptions\HTTPRequestException;
use MeiliSearch\Exceptions\TimeOutException;

class Indexes extends Endpoint
{
    use HandlesDocuments;
    use HandlesSettings;

    protected const PATH = '/indexes';

    /**
     * @var string|null
     */
    private $uid;

    public function __construct(Http $http, $uid = null)
    {
        $this->uid = $uid;
        parent::__construct($http);
    }

    /**
     * @return $this
     *
     * @throws Exception|HTTPRequestException
     */
    public function create(string $uid, array $options = []): self
    {
        $options['uid'] = $uid;

        $response = $this->http->post(self::PATH, $options);

        return new self($this->http, $response['uid']);
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
        return $this->show()['primaryKey'];
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function show(): ?array
    {
        return $this->http->get(self::PATH.'/'.$this->uid);
    }

    public function update($body): array
    {
        return  $this->http->put(self::PATH.'/'.$this->uid, $body);
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
     * @param $updateId
     * @param int $timeoutInMs
     * @param int $intervalInMs
     *
     * @return mixed
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

    public function search($query, array $options = []): array
    {
        $parameters = array_merge(
            ['q' => $query],
            $options
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
