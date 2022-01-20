<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class FilterableAttributesTest extends TestCase
{
    public function testGetDefaultFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex('index');

        $attributes = $index->getFilterableAttributes();

        $this->assertEmpty($attributes);
    }

    public function testUpdateFilterableAttributes(): void
    {
        $newAttributes = ['title'];
        $index = $this->createEmptyIndex('index');

        $promise = $index->updateFilterableAttributes($newAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['uid']);

        $filterableAttributes = $index->getFilterableAttributes();

        $this->assertEquals($newAttributes, $filterableAttributes);
    }

    public function testResetFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex('index');
        $newAttributes = ['title'];

        $promise = $index->updateFilterableAttributes($newAttributes);
        $index->waitForTask($promise['uid']);

        $promise = $index->resetFilterableAttributes();

        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['uid']);

        $filterableAttributes = $index->getFilterableAttributes();
        $this->assertEmpty($filterableAttributes);
    }
}
