<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class FilterableAttributesTest extends TestCase
{
    public function testGetDefaultFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $attributes = $index->getFilterableAttributes();

        $this->assertEmpty($attributes);
    }

    public function testUpdateFilterableAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateFilterableAttributes($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $filterableAttributes = $index->getFilterableAttributes();

        $this->assertEquals($newAttributes, $filterableAttributes);
    }

    public function testResetFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $newAttributes = ['title'];

        $promise = $index->updateFilterableAttributes($newAttributes);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetFilterableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);

        $filterableAttributes = $index->getFilterableAttributes();
        $this->assertEmpty($filterableAttributes);
    }
}
