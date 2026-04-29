<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Index;
use Tests\TestCase;

final class SearchCutoffMsTest extends TestCase
{
    private Index $index;

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
        $this->index->updateSearchCutoffMs(50)->wait();

        self::assertSame(50, $this->index->getSearchCutoffMs());
    }

    public function testResetSearchCutoffMs(): void
    {
        $this->index->updateSearchCutoffMs(50)->wait();
        $this->index->resetSearchCutoffMs()->wait();

        self::assertNull($this->index->getSearchCutoffMs());
    }
}
