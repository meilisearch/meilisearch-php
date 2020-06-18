<?php

namespace MeiliSearch;

use MeiliSearch\Delegates\handlesIndex;
use MeiliSearch\Delegates\handlesSystem;
use MeiliSearch\Endpoints\Health;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Endpoints\Keys;
use MeiliSearch\Endpoints\Stats;
use MeiliSearch\Endpoints\SysInfo;
use MeiliSearch\Endpoints\Version;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    use handlesIndex, handlesSystem;

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
     * @var SysInfo
     */
    private $sysInfo;
    /**
     * @var Keys
     */
    private $keys;
    /**
     * @var Stats
     */
    private $stats;


    public function __construct($url, $apiKey = null, ClientInterface $httpClient = null, RequestFactoryInterface $requestFactory = null, StreamFactoryInterface $streamFactory = null)
    {
        $this->http    = new Http\Client($url, $apiKey, $httpClient, $requestFactory, $streamFactory);
        $this->index   = new Indexes($this->http);
        $this->health  = new Health($this->http);
        $this->version = new Version($this->http);
        $this->sysInfo = new SysInfo($this->http);
        $this->stats   = new Stats($this->http);
        $this->keys    = new Keys($this->http);
    }
}
