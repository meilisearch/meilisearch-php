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

        $task = $this->index->updateDistinctAttribute($distinctAttribute);
        $this->index->waitForTask($task->getTaskUid());

        self::assertSame($distinctAttribute, $this->index->getDistinctAttribute());
    }

    public function testResetDistinctAttribute(): void
    {
        $distinctAttribute = 'description';

        $task = $this->index->updateDistinctAttribute($distinctAttribute);
        $this->index->waitForTask($task->getTaskUid());

        $task = $this->index->resetDistinctAttribute();
        $this->index->waitForTask($task->getTaskUid());

        self::assertNull($this->index->getDistinctAttribute());
    }
}
