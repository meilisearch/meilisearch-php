<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SortableAttributesTest extends TestCase
{
    public function testGetDefaultSortableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $attributes = $index->getSortableAttributes();

        $this->assertEmpty($attributes);
    }

    public function testUpdateSortableAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateSortableAttributes($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $sortableAttributes = $index->getSortableAttributes();

        $this->assertEquals($newAttributes, $sortableAttributes);
    }

    public function testResetSortableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $newAttributes = ['title'];

        $promise = $index->updateSortableAttributes($newAttributes);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetSortableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);

        $sortableAttributes = $index->getSortableAttributes();
        $this->assertEmpty($sortableAttributes);
    }
}
