<?php

declare(strict_types=1);

namespace MeiliSearch;

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
use MeiliSearch\Endpoints\Version;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class Client
{
    use HandlesDumps;
    use HandlesIndex;
    use HandlesTasks;
    use HandlesKeys;
    use HandlesSystem;

    private $http;
    private Indexes $index;
    private Health $health;
    private Version $version;
    private Keys $keys;
    private Stats $stats;
    private Tasks $tasks;
    private Dumps $dumps;

    public function __construct(
        string $url,
        string $apiKey = null,
        ClientInterface $httpClient = null,
        RequestFactoryInterface $requestFactory = null
    ) {
        $this->apiKey = $apiKey;
        $this->http = new Http\Client($url, $apiKey, $httpClient, $requestFactory);
        $this->index = new Indexes($this->http);
        $this->health = new Health($this->http);
        $this->version = new Version($this->http);
        $this->stats = new Stats($this->http);
        $this->tasks = new Tasks($this->http);
        $this->keys = new Keys($this->http);
        $this->dumps = new Dumps($this->http);
    }
}
