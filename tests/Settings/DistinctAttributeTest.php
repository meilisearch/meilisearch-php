<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class DistinctAttributeTest extends TestCase
{
    private $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->client->createIndex('index');
    }

    public function testGetDefaultDistinctAttribute(): void
    {
        $response = $this->index->getDistinctAttribute();
        $this->assertNull($response);
    }

    public function testUpdateDistinctAttribute(): void
    {
        $distinctAttribute = 'description';
        $promise = $this->index->updateDistinctAttribute($distinctAttribute);

        $this->assertIsValidPromise($promise);
        $this->index->waitForPendingUpdate($promise['updateId']);

        $this->assertEquals($distinctAttribute, $this->index->getDistinctAttribute());
    }

    public function testResetDistinctAttribute(): void
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
