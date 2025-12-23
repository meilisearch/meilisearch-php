<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\NetworkResults;
use Meilisearch\Contracts\Task;

use function Meilisearch\partial;

/**
 * @phpstan-import-type RemoteConfig from NetworkResults
 */
class Network extends Endpoint
{
    protected const PATH = '/network';

    /**
     * @return array{
     *     self?: non-empty-string|null,
     *     leader?: non-empty-string|null,
     *     version?: non-empty-string|null,
     *     remotes?: array<non-empty-string, RemoteConfig|null>
     * }
     */
    public function get(): array
    {
        return $this->http->get(self::PATH);
    }

    /**
     * Initialize a network with the current instance as leader.
     *
     * @param array{
     *     self: non-empty-string,
     *     remotes: array<non-empty-string, RemoteConfig>
     * } $options
     */
    public function initialize(array $options): Task
    {
        $body = [
            'self' => $options['self'],
            'leader' => $options['self'],
            'remotes' => $options['remotes'],
        ];

        return Task::fromArray($this->http->patch(self::PATH, $body), partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * Add a remote to the network.
     *
     * @param non-empty-string $name
     * @param RemoteConfig     $remote
     */
    public function addRemote(string $name, array $remote): Task
    {
        $body = [
            'remotes' => [
                $name => $remote,
            ],
        ];

        return Task::fromArray($this->http->patch(self::PATH, $body), partial(Tasks::waitTask(...), $this->http));
    }

    /**
     * Remove a remote from the network.
     *
     * @param non-empty-string $name
     */
    public function removeRemote(string $name): Task
    {
        $body = [
            'remotes' => [
                $name => null,
            ],
        ];

        return Task::fromArray($this->http->patch(self::PATH, $body), partial(Tasks::waitTask(...), $this->http));
    }
}
