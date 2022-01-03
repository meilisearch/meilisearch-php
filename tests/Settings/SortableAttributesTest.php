<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SortableAttributesTest extends TestCase
{
    public function testGetDefaultSortableAttributes(): void
    {
        $index = $this->createEmptyIndex('index');

        $attributes = $index->getSortableAttributes();

        $this->assertIsArray($attributes);
        $this->assertEmpty($attributes);
    }

    public function testUpdateSortableAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->createEmptyIndex('index');

        $promise = $index->updateSortableAttributes($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['uid']);

        $sortableAttributes = $index->getSortableAttributes();

        $this->assertIsArray($sortableAttributes);
        $this->assertEquals($newAttributes, $sortableAttributes);
    }

    public function testResetSortableAttributes(): void
    {
        $index = $this->createEmptyIndex('index');
        $newAttributes = ['title'];

        $promise = $index->updateSortableAttributes($newAttributes);
        $index->waitForTask($promise['uid']);

        $promise = $index->resetSortableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);

        $sortableAttributes = $index->getSortableAttributes();
        $this->assertIsArray($sortableAttributes);
        $this->assertEmpty($sortableAttributes);
    }
}
