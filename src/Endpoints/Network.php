<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\NetworkResults;
use Meilisearch\Contracts\Task;

use function Meilisearch\partial;

/**
 * @phpstan-import-type RemoteConfig from NetworkResults
 *
 * @phpstan-type ShardConfig array{
 *   remotes?: list<non-empty-string>,
 *   addRemotes?: list<non-empty-string>,
 *   removeRemotes?: list<non-empty-string>
 * }
 */
class Network extends Endpoint
{
    protected const PATH = '/network';

    /**
     * @return array{
     *     self?: non-empty-string|null,
     *     leader?: non-empty-string|null,
     *     version?: non-empty-string|null,
     *     remotes?: array<non-empty-string, RemoteConfig|null>,
     *     shards?: array<non-empty-string, array{remotes: list<non-empty-string>}>
     * }
     */
    public function get(): array
    {
        return $this->http->get(self::PATH);
    }

    /**
     * @param array{
     *     self: non-empty-string,
     *     leader: non-empty-string,
     *     remotes: array<non-empty-string, RemoteConfig>,
     *     shards: array<non-empty-string, ShardConfig>,
     * } $options
     */
    public function initialize(array $options): Task
    {
        $body = [
            'self' => $options['self'],
            'leader' => $options['leader'],
            'remotes' => $options['remotes'],
            'shards' => $options['shards'],
        ];

        return $this->dispatchPatch($body);
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

        return $this->dispatchPatch($body);
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

        return $this->dispatchPatch($body);
    }

    /**
     * @param non-empty-string       $shardName
     * @param list<non-empty-string> $remoteNames
     */
    public function addRemotesToShard(string $shardName, array $remoteNames): Task
    {
        $body = [
            'shards' => [
                $shardName => ['addRemotes' => $remoteNames],
            ],
        ];

        return $this->dispatchPatch($body);
    }

    /**
     * @param non-empty-string       $shardName
     * @param list<non-empty-string> $remoteNames
     */
    public function removeRemotesFromShard(string $shardName, array $remoteNames): Task
    {
        $body = [
            'shards' => [
                $shardName => ['removeRemotes' => $remoteNames],
            ],
        ];

        return $this->dispatchPatch($body);
    }

    /**
     * @param array<mixed> $body
     */
    private function dispatchPatch(array $body): Task
    {
        return Task::fromArray($this->http->patch(self::PATH, $body), partial(Tasks::waitTask(...), $this->http));
    }
}
