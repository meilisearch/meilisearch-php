<?php

use MeiliSearch\Client;
use Tests\TestCase;

class StopWordsTest extends TestCase
{
    private $client;
    private $index;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client('http://localhost:7700', 'masterKey');
    }

    protected function setUp():void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
        $this->index = $this->client->createIndex('index');
    }

    public function testGetDefaultStopWords()
    {
        $response = $this->index->getStopWords();

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    public function testUpdateStopWords()
    {
        $newStopWords = ['the'];
        $promise = $this->index->updateStopWords($newStopWords);

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);

        $this->index->waitForPendingUpdate($promise['updateId']);
        $stopWords = $this->index->getStopWords();

        $this->assertIsArray($stopWords);
        $this->assertEquals($newStopWords, $stopWords);
    }

    public function testResetStopWords()
    {
        $promise = $this->index->updateStopWords(['the']);
        $this->index->waitForPendingUpdate($promise['updateId']);

        $promise = $this->index->resetStopWords();

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $this->index->waitForPendingUpdate($promise['updateId']);

        $topWords = $this->index->getStopWords();

        $this->assertIsArray($topWords);
        $this->assertEmpty($topWords);
    }
}
