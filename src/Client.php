<?php

declare(strict_types=1);

namespace Meilisearch;

use Meilisearch\Endpoints\Batches;
use Meilisearch\Endpoints\Delegates\HandlesBatches;
use Meilisearch\Endpoints\Delegates\HandlesDumps;
use Meilisearch\Endpoints\Delegates\HandlesIndex;
use Meilisearch\Endpoints\Delegates\HandlesKeys;
use Meilisearch\Endpoints\Delegates\HandlesMultiSearch;
use Meilisearch\Endpoints\Delegates\HandlesSnapshots;
use Meilisearch\Endpoints\Delegates\HandlesSystem;
use Meilisearch\Endpoints\Delegates\HandlesTasks;
use Meilisearch\Endpoints\Dumps;
use Meilisearch\Endpoints\Health;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Endpoints\Keys;
use Meilisearch\Endpoints\Snapshots;
use Meilisearch\Endpoints\Stats;
use Meilisearch\Endpoints\Tasks;
use Meilisearch\Endpoints\TenantToken;
use Meilisearch\Endpoints\Version;
use Meilisearch\Http\Client as MeilisearchClientAdapter;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    use HandlesDumps;
    use HandlesIndex;
    use HandlesTasks;
    use HandlesKeys;
    use HandlesSnapshots;
    use HandlesSystem;
    use HandlesMultiSearch;
    use HandlesBatches;

    /**
     * @param array<int, string> $clientAgents
     */
    public function __construct(
        string $url,
        ?string $apiKey = null,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        array $clientAgents = [],
        ?StreamFactoryInterface $streamFactory = null,
        $headers = []
    ) {
        $this->http = new MeilisearchClientAdapter($url, $apiKey, $httpClient, $requestFactory, $clientAgents, $streamFactory, $headers);
        $this->index = new Indexes($this->http);
        $this->health = new Health($this->http);
        $this->version = new Version($this->http);
        $this->stats = new Stats($this->http);
        $this->tasks = new Tasks($this->http);
        $this->batches = new Batches($this->http);
        $this->keys = new Keys($this->http);
        $this->dumps = new Dumps($this->http);
        $this->snapshots = new Snapshots($this->http);
        $this->tenantToken = new TenantToken($this->http, $apiKey);
    }
}
