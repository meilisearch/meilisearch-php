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
        $promise = $this->index->updateSearchCutoffMs(50);
        $this->index->waitForTask($promise['taskUid']);

        self::assertSame(50, $this->index->getSearchCutoffMs());
    }

    public function testResetSearchCutoffMs(): void
    {
        $promise = $this->index->updateSearchCutoffMs(50);
        $this->index->waitForTask($promise['taskUid']);

        $promise = $this->index->resetSearchCutoffMs();
        $this->index->waitForTask($promise['taskUid']);

        self::assertNull($this->index->getSearchCutoffMs());
    }
}
