<?php

declare(strict_types=1);

namespace Meilisearch;

use Meilisearch\Contracts\Http as HttpContract;
use Meilisearch\Endpoints\Delegates\HandlesDumps;
use Meilisearch\Endpoints\Delegates\HandlesIndex;
use Meilisearch\Endpoints\Delegates\HandlesKeys;
use Meilisearch\Endpoints\Delegates\HandlesSystem;
use Meilisearch\Endpoints\Delegates\HandlesTasks;
use Meilisearch\Endpoints\Dumps;
use Meilisearch\Endpoints\Health;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Endpoints\Keys;
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
    use HandlesSystem;

    private HttpContract $http;
    private Indexes $index;
    private Health $health;
    private Version $version;
    private Keys $keys;
    private Stats $stats;
    private Tasks $tasks;
    private Dumps $dumps;
    private TenantToken $tenantToken;

    public function __construct(
        string $url,
        string $apiKey = null,
        ClientInterface $httpClient = null,
        RequestFactoryInterface $requestFactory = null,
        array $clientAgents = [],
        StreamFactoryInterface $streamFactory = null
    ) {
        $this->http = new MeilisearchClientAdapter($url, $apiKey, $httpClient, $requestFactory, $clientAgents, $streamFactory);
        $this->index = new Indexes($this->http);
        $this->health = new Health($this->http);
        $this->version = new Version($this->http);
        $this->stats = new Stats($this->http);
        $this->tasks = new Tasks($this->http);
        $this->keys = new Keys($this->http);
        $this->dumps = new Dumps($this->http);
        $this->tenantToken = new TenantToken($this->http, $apiKey);
    }
}
