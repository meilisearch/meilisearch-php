<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\NetworkResults;
use Meilisearch\Endpoints\Network;

trait HandlesNetwork
{
    protected Network $network;

    public function getNetwork(): NetworkResults
    {
        $response = $this->network->get();

        return new NetworkResults($response);
    }

    /**
     * @param array{
     *     self?: non-empty-string,
     *     remotes?: array<non-empty-string, array{url: non-empty-string, searchApiKey: string}>
     * } $network
     */
    public function updateNetwork(array $network): NetworkResults
    {
        $response = $this->network->update($network);

        return new NetworkResults($response);
    }
}
