<?php


namespace MeiliSearch\Delegates;


use MeiliSearch\Endpoints\Index;

trait handlesIndex
{
    public function getAllIndexes()
    {
        return $this->index->all();
    }

    public function showIndex($uid)
    {
        return (new \MeiliSearch\Endpoints\Index($this->http, $uid))->show();
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
}