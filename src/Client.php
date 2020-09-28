<?php

declare(strict_types=1);

namespace MeiliSearch;

use MeiliSearch\Delegates\HandlesIndex;
use MeiliSearch\Delegates\HandlesSystem;
use MeiliSearch\Endpoints\Health;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Endpoints\Keys;
use MeiliSearch\Endpoints\Stats;
use MeiliSearch\Endpoints\Version;

class Client
{
    use HandlesIndex;
    use HandlesSystem;

    private $http;

    /**
     * @var Indexes
     */
    private $index;

    /**
     * @var Health
     */
    private $health;

    /**
     * @var Version
     */
    private $version;

    /**
     * @var Keys
     */
    private $keys;

    /**
     * @var Stats
     */
    private $stats;

    public function __construct(string $url, string $apiKey = null)
    {
        $this->http = new Http\Client($url, $apiKey);
        $this->index = new Indexes($this->http);
        $this->health = new Health($this->http);
        $this->version = new Version($this->http);
        $this->stats = new Stats($this->http);
        $this->keys = new Keys($this->http);
    }
}
