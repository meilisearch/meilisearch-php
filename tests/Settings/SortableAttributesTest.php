<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SortableAttributesTest extends TestCase
{
    public function testGetDefaultSortableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        self::assertEmpty($index->getSortableAttributes());
    }

    public function testUpdateSortableAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateSortableAttributes($newAttributes);
        $index->waitForTask($promise['taskUid']);

        self::assertSame($newAttributes, $index->getSortableAttributes());
    }

    public function testResetSortableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $newAttributes = ['title'];

        $promise = $index->updateSortableAttributes($newAttributes);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetSortableAttributes();
        $index->waitForTask($promise['taskUid']);

        self::assertEmpty($index->getSortableAttributes());
    }
}
