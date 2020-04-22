<?php

use MeiliSearch\Client;
use Tests\TestCase;

class DistinctAttributeTest extends TestCase
{
    private $client;
    private $index;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client('http://localhost:7700', 'masterKey');
        $this->client->deleteAllIndexes();
        $this->index = $this->client->createIndex('index');
    }

    public function testGetDefaultDistinctAttribute()
    {
        $response = $this->index->getDistinctAttribute();
        $this->assertNull($response);
    }

    public function testUpdateDistinctAttribute()
    {
        $distinctAttribute = 'description';
        $promise = $this->index->updateDistinctAttribute($distinctAttribute);
        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $this->index->waitForPendingUpdate($promise['updateId']);
        $this->assertEquals($distinctAttribute, $this->index->getDistinctAttribute());
    }

    public function testResetDistinctAttribute()
    {
        $distinctAttribute = 'description';
        $promise = $this->index->updateDistinctAttribute($distinctAttribute);
        $this->index->waitForPendingUpdate($promise['updateId']);

        $promise = $this->index->resetDistinctAttribute();

        $this->assertIsArray($promise);
        $this->assertArrayHasKey('updateId', $promise);
        $this->index->waitForPendingUpdate($promise['updateId']);
        $this->assertNull($this->index->getDistinctAttribute());
    }
}
