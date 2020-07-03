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
     * @param array $options
     *
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

    public function delete(): void
    {
        $this->http->delete(self::PATH.'/'.$this->uid);
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
     * @param $update_id
     * @param int $timeout_in_ms
     * @param int $interval_in_ms
     *
     * @return mixed
     *
     * @throws TimeOutException
     */
    public function waitForPendingUpdate($update_id, $timeout_in_ms = 5000, $interval_in_ms = 50): array
    {
        $timeout_temp = 0;
        while ($timeout_in_ms > $timeout_temp) {
            $res = $this->getUpdateStatus($update_id);
            if ('enqueued' != $res['status']) {
                return $res;
            }
            $timeout_temp += $interval_in_ms;
            usleep(1000 * $interval_in_ms);
        }
        throw new TimeOutException();
    }

    // Search

    public function search($query, array $options = []): array
    {
        $parameters = array_merge(
            ['q' => $query],
            $this->parseOptions($options)
        );

        return $this->http->get(self::PATH.'/'.$this->uid.'/search', $parameters);
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

    private function parseOptions(array $options): array
    {
        foreach ($options as $key => $value) {
            if ('facetsDistribution' === $key || 'facetFilters' === $key) {
                $options[$key] = json_encode($value);
            } elseif (\is_array($value)) {
                $options[$key] = implode(',', $value);
            }
        }

        return $options;
    }
}
