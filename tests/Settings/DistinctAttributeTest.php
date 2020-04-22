<?php

use Tests\TestCase;

class DistinctAttributeTest extends TestCase
{
    private $index;

    public function __construct()
    {
        parent::__construct();
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
        $this->assertIsValidPromise($promise);
        $this->index->waitForPendingUpdate($promise['updateId']);
        $this->assertEquals($distinctAttribute, $this->index->getDistinctAttribute());
    }

    public function testResetDistinctAttribute()
    {
        $distinctAttribute = 'description';
        $promise = $this->index->updateDistinctAttribute($distinctAttribute);
        $this->index->waitForPendingUpdate($promise['updateId']);

        $promise = $this->index->resetDistinctAttribute();

        $this->assertIsValidPromise($promise);
        $this->index->waitForPendingUpdate($promise['updateId']);
        $this->assertNull($this->index->getDistinctAttribute());
    }
}
