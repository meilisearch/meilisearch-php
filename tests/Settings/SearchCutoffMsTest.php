<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class SearchCutoffMsTest extends TestCase
{
    private Indexes $index;

    protected function setUp(): void
    {
        parent::setUp();
        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultSearchCutoffMs(): void
    {
        $default = $this->index->getSearchCutoffMs();

        self::assertNull($default);
    }

    public function testUpdateSearchCutoffMs(): void
    {
        $task = $this->index->updateSearchCutoffMs(50);
        $this->index->waitForTask($task->getTaskUid());

        self::assertSame(50, $this->index->getSearchCutoffMs());
    }

    public function testResetSearchCutoffMs(): void
    {
        $task = $this->index->updateSearchCutoffMs(50);
        $this->index->waitForTask($task->getTaskUid());

        $task = $this->index->resetSearchCutoffMs();
        $this->index->waitForTask($task->getTaskUid());

        self::assertNull($this->index->getSearchCutoffMs());
    }
}
