<?php

namespace MeiliSearch;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Client
{
    private $http;
    /**
     * @var Index
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


    public function __construct($url, $apiKey = null, ClientInterface $httpClient = null, RequestFactoryInterface $requestFactory = null, StreamFactoryInterface $streamFactory = null)
    {
        $this->http = new Http\Client($url, $apiKey, $httpClient, $requestFactory, $streamFactory);
        $this->index = new Index($this->http);
        $this->health = new Health($this->http);
        $this->version = new Version($this->http);
        $this->sysInfo = new SysInfo($this->http);
        $this->stats = new Stats($this->http);
    }

    public function getAllIndexes()
    {
        return $this->index->all();
    }

    public function showIndex($uid)
    {
        return (new Index($this->http, $uid))->show();
    }

    public function deleteIndex($uid)
    {
        return (new Index($this->http, $uid))->delete();
    }

    public function deleteAllIndexes()
    {
        $indexes = $this->getAllIndexes();
        foreach ($indexes as $index) {
            $index->delete();
        }
    }

    public function getIndex($uid)
    {
        return new Index($this->http, $uid);
    }

    public function createIndex($uid, $options = [])
    {
        return $this->index->create($uid, $options);
    }

    // Health

    public function health()
    {
        return $this->health->show();
    }

    // Stats

    public function version()
    {
        return $this->version->show();
    }

    public function sysInfo()
    {
        return $this->sysInfo->show();
    }

    public function prettySysInfo()
    {
        return $this->sysInfo->pretty();
    }

    public function stats()
    {
        return $this->stats->show();
    }

    // Keys

    public function getKeys()
    {
        return $this->httpGet('/keys');
    }

    // Private methods

    private function indexInstance($uid)
    {
        return new Index($uid, $this->base_url, $this->api_key);
    }
}
