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

        self::assertNull($response);
    }

    public function testUpdateDistinctAttribute(): void
    {
        $distinctAttribute = 'description';

        $promise = $this->index->updateDistinctAttribute($distinctAttribute);
        $this->index->waitForTask($promise['taskUid']);

        self::assertSame($distinctAttribute, $this->index->getDistinctAttribute());
    }

    public function testResetDistinctAttribute(): void
    {
        $distinctAttribute = 'description';

        $promise = $this->index->updateDistinctAttribute($distinctAttribute);
        $this->index->waitForTask($promise['taskUid']);

        $promise = $this->index->resetDistinctAttribute();
        $this->index->waitForTask($promise['taskUid']);

        self::assertNull($this->index->getDistinctAttribute());
    }
}
