<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class ProximityPrecisionTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultProximityPrecision(): void
    {
        $default = $this->index->getProximityPrecision();

        self::assertSame('byWord', $default);
    }

    public function testUpdateProximityPrecision(): void
    {
        $task = $this->index->updateProximityPrecision('byAttribute');
        $this->index->waitForTask($task->getTaskUid());

        self::assertSame('byAttribute', $this->index->getProximityPrecision());
    }

    public function testResetProximityPrecision(): void
    {
        $task = $this->index->updateProximityPrecision('byAttribute');
        $this->index->waitForTask($task->getTaskUid());

        $task = $this->index->resetProximityPrecision();
        $this->index->waitForTask($task->getTaskUid());

        self::assertSame('byWord', $this->index->getProximityPrecision());
    }
}
