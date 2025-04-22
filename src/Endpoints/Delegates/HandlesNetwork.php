<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\NetworkResults;
use Meilisearch\Endpoints\Network;

trait HandlesNetwork
{
    /**
     * @var Network
     */
    protected Network $network;

    /**
     * @return NetworkResults
     */
    public function getNetwork(): NetworkResults
    {
        $response = $this->network->get();

        return new NetworkResults($response);
    }

    /**
     * @param array{
     *     self?: string,
     *     remotes?: array<string, array{url: string, searchApiKey: string}>
     * } $network
     *
     * @return NetworkResults
     */
    public function updateNetwork(array $network): NetworkResults
    {
        $response = $this->network->update($network);

        return new NetworkResults($response);
    }
}
