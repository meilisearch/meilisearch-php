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

        $this->index->updateDistinctAttribute($distinctAttribute)->wait();

        self::assertSame($distinctAttribute, $this->index->getDistinctAttribute());
    }

    public function testResetDistinctAttribute(): void
    {
        $distinctAttribute = 'description';

        $this->index->updateDistinctAttribute($distinctAttribute)->wait();
        $this->index->resetDistinctAttribute()->wait();

        self::assertNull($this->index->getDistinctAttribute());
    }
}
