<?php

namespace MeiliSearch;

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

    public function getIndex($uid)
    {
        return $this->indexInstance($uid);
    }

    public function createIndex($attributes)
    {
        if (is_array($attributes)) {
            $body = $attributes;
        } else {
            $body = ['uid' => $attributes];
        }
        $response = $this->httpPost('/indexes', $body);
        $uid = $response['uid'];

        return $this->indexInstance($uid);
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
