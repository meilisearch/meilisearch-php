<?php

declare(strict_types=1);

namespace Tests\Settings;

use Meilisearch\Endpoints\Indexes;
use Tests\TestCase;

final class PaginationTest extends TestCase
{
    private Indexes $index;

    public const DEFAULT_PAGINATION = [
        'maxTotalHits' => 1000,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->index = $this->createEmptyIndex($this->safeIndexName());
    }

    public function testGetDefaultPagination(): void
    {
        $response = $this->index->getPagination();

        self::assertSame(self::DEFAULT_PAGINATION, $response);
    }

    public function testUpdatePagination(): void
    {
        $this->index->updatePagination(['maxTotalHits' => 100])->wait();

        self::assertSame(['maxTotalHits' => 100], $this->index->getPagination());
    }

    public function testResetPagination(): void
    {
        $this->index->updatePagination(['maxTotalHits' => 100])->wait();
        $this->index->resetPagination()->wait();

        self::assertSame(self::DEFAULT_PAGINATION, $this->index->getPagination());
    }
}
