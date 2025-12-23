<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\NetworkResults;
use Meilisearch\Contracts\Task;
use Meilisearch\Endpoints\Network;

/**
 * @phpstan-import-type RemoteConfig from NetworkResults
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
     *     remotes: array<non-empty-string, RemoteConfig>
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
}
