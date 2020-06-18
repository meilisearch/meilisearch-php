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

    public function __construct($url, $apiKey = null, ClientInterface $httpClient = null, RequestFactoryInterface $requestFactory = null, StreamFactoryInterface $streamFactory = null)
    {
        $this->http = new Http\Client($url, $apiKey, $httpClient, $requestFactory, $streamFactory);
        $this->index = new Index($this->http);
    }

    public function getAllIndexes()
    {
        return $this->index->all();
    }

    public function showIndex($uid)
    {
        return $this->indexInstance($uid)->show();
    }

    public function deleteIndex($uid)
    {
        return $this->indexInstance($uid)->delete();
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
        return new Index($uid);
    }

    public function createIndex($index_uid, $options = [])
    {
        return $this->index->create($index_uid, $options);
//        $body = array_merge(
//            $options,
//            ['uid' => $index_uid]
//        );
//        $response = $this->httpPost('/indexes', $body);
//        $uid = $response['uid'];
//
//        return $this->indexInstance($uid);
    }

    // Health

    public function health()
    {
        return $this->httpGet('/health');
    }

    // Stats

    public function version()
    {
        return $this->httpGet('/version');
    }

    public function sysInfo()
    {
        return $this->httpGet('/sys-info');
    }

    public function prettySysInfo()
    {
        return $this->httpGet('/sys-info/pretty');
    }

    public function stats()
    {
        return $this->httpGet('/stats');
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
