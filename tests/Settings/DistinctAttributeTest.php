<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class DistinctAttributeTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
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
        $this->index->waitForTask($promise['taskUid']);

        $this->assertEquals($distinctAttribute, $this->index->getDistinctAttribute());
    }

    public function testResetDistinctAttribute(): void
    {
        $distinctAttribute = 'description';
        $promise = $this->index->updateDistinctAttribute($distinctAttribute);
        $this->index->waitForTask($promise['taskUid']);

        $promise = $this->index->resetDistinctAttribute();

        $this->assertIsValidPromise($promise);
        $this->index->waitForTask($promise['taskUid']);
        $this->assertNull($this->index->getDistinctAttribute());
    }
}
