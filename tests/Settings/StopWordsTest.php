<?php

use Tests\TestCase;

class StopWordsTest extends TestCase
{
    private $index;

    protected function setUp(): void
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

        $this->assertIsValidPromise($promise);

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

        $this->assertIsValidPromise($promise);
        $this->index->waitForPendingUpdate($promise['updateId']);

        $topWords = $this->index->getStopWords();

        $this->assertIsArray($topWords);
        $this->assertEmpty($topWords);
    }
}
