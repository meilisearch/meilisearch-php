<?php

namespace MeiliSearch;

use MeiliSearch\Exceptions\HTTPRequestException;

class Client extends HTTPRequest
{
    // Indexes

    public function getAllIndexes()
    {
        return $this->httpGet('/indexes');
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
        foreach ($this->getAllIndexes() as $index) {
            $this->deleteIndex($index['uid']);
        }
    }

    public function getIndex($uid)
    {
        return $this->indexInstance($uid);
    }

    public function createIndex($index_uid, $options = [])
    {
        $body = array_merge(
            $options,
            ['uid' => $index_uid]
        );
        $response = $this->httpPost('/indexes', $body);
        $uid = $response['uid'];

        return $this->indexInstance($uid);
    }

    /**
     * @throws HTTPRequestException
     */
    public function getOrCreateIndex(string $uid, array $options = []): Index
    {
        $index = $this->getIndex($uid);

        try {
            $index = $this->createIndex($uid, $options);
        } catch (HTTPRequestException $e) {
            if (is_array($e->http_body) && 'index_already_exists' !== $e->http_body['errorCode']) {
                throw $e;
            }
        }

        return $index;
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
