<?php

declare(strict_types=1);

namespace MeiliSearch;

use MeiliSearch\Contracts\Http as HttpContract;
use MeiliSearch\Delegates\HandlesIndex;
use MeiliSearch\Delegates\HandlesSystem;
use MeiliSearch\Endpoints\Delegates\HandlesDumps;
use MeiliSearch\Endpoints\Delegates\HandlesKeys;
use MeiliSearch\Endpoints\Delegates\HandlesTasks;
use MeiliSearch\Endpoints\Dumps;
use MeiliSearch\Endpoints\Health;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Endpoints\Keys;
use MeiliSearch\Endpoints\Stats;
use MeiliSearch\Endpoints\Tasks;
use MeiliSearch\Endpoints\TenantToken;
use MeiliSearch\Endpoints\Version;
use MeiliSearch\Http\Client as MeilisearchClientAdapter;
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
