<?php

use MeiliSearch\Client;
use Tests\TestCase;

class SynonymsTest extends TestCase
{
    private $client;
    private $index;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client('http://localhost:7700', 'masterKey');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->deleteAllIndexes();
        $this->index = $this->client->createIndex('index');
    }

    public function testGetDefaultSynonyms()
    {
        $response = $this->index->getSynonyms();

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    public function testUpdateSynonyms()
    {
        $newSynonyms = [
            'hp' => ['harry potter'],
        ];
        $promise = $this->index->updateSynonyms($newSynonyms);

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);

        $this->index->waitForPendingUpdate($promise['updateId']);
        $synonyms = $this->index->getSynonyms();

        $this->assertIsArray($synonyms);
        $this->assertEquals($newSynonyms, $synonyms);
    }

    public function testResetSynonyms()
    {
        $promise = $this->index->updateSynonyms([
            'hp' => ['harry potter'],
        ]);
        $this->index->waitForPendingUpdate($promise['updateId']);
        $promise = $this->index->resetSynonyms();

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);

        $this->index->waitForPendingUpdate($promise['updateId']);
        $synonyms = $this->index->getSynonyms();

        $this->assertIsArray($synonyms);
        $this->assertEmpty($synonyms);
    }
}
