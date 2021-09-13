<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class SortableAttributesTest extends TestCase
{
    public function testGetDefaultSortableAttributes(): void
    {
        $index = $this->client->createIndex('index');

        $attributes = $index->getSortableAttributes();

        $this->assertIsArray($attributes);
        $this->assertEmpty($attributes);
    }

    public function testUpdateSortableAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->client->createIndex('index');

        $promise = $index->updateSortableAttributes($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForPendingUpdate($promise['updateId']);

        $sortableAttributes = $index->getSortableAttributes();

        $this->assertIsArray($sortableAttributes);
        $this->assertEquals($newAttributes, $sortableAttributes);
    }

    public function testResetSortableAttributes(): void
    {
        $index = $this->client->createIndex('index');
        $newAttributes = ['title'];

        $promise = $index->updateSortableAttributes($newAttributes);
        $index->waitForPendingUpdate($promise['updateId']);

        $promise = $index->resetSortableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForPendingUpdate($promise['updateId']);

        $sortableAttributes = $index->getSortableAttributes();
        $this->assertIsArray($sortableAttributes);
        $this->assertEmpty($sortableAttributes);
    }
}
