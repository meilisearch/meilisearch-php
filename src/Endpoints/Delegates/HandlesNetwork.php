<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\NetworkResults;
use Meilisearch\Contracts\Task;
use Meilisearch\Endpoints\Network;

/**
 * @phpstan-import-type RemoteConfig from NetworkResults
 * @phpstan-import-type ShardConfig from \Meilisearch\Endpoints\Network
 */
trait HandlesNetwork
{
    protected Network $network;

    public function getNetwork(): NetworkResults
    {
        $response = $this->network->get();

        return new NetworkResults($response);
    }

    /**
     * Initialize a network with the current instance as leader.
     *
     * @param array{
     *     self: non-empty-string,
     *     leader: non-empty-string,
     *     remotes: array<non-empty-string, RemoteConfig>,
     *     shards: array<non-empty-string, ShardConfig>,
     * } $options
     */
    public function initializeNetwork(array $options): Task
    {
        return $this->network->initialize($options);
    }

    /**
     * Add a remote to the network.
     *
     * @param non-empty-string $name
     * @param RemoteConfig     $remote
     */
    public function addRemote(string $name, array $remote): Task
    {
        return $this->network->addRemote($name, $remote);
    }

    /**
     * Remove a remote from the network.
     *
     * @param non-empty-string $name
     */
    public function removeRemote(string $name): Task
    {
        return $this->network->removeRemote($name);
    }

    /**
     * @param non-empty-string       $shardName
     * @param list<non-empty-string> $remoteNames
     */
    public function addRemotesToShard(string $shardName, array $remoteNames): Task
    {
        return $this->network->addRemotesToShard($shardName, $remoteNames);
    }

    /**
     * @param non-empty-string       $shardName
     * @param list<non-empty-string> $remoteNames
     */
    public function removeRemotesFromShard(string $shardName, array $remoteNames): Task
    {
        return $this->network->removeRemotesFromShard($shardName, $remoteNames);
    }
}
