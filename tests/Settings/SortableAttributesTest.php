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

        $task = $index->updateSortableAttributes($newAttributes);
        $index->waitForTask($task['taskUid']);

        self::assertSame($newAttributes, $index->getSortableAttributes());
    }

    public function testResetSortableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $newAttributes = ['title'];

        $task = $index->updateSortableAttributes($newAttributes);
        $index->waitForTask($task['taskUid']);

        $task = $index->resetSortableAttributes();
        $index->waitForTask($task['taskUid']);

        self::assertEmpty($index->getSortableAttributes());
    }
}
