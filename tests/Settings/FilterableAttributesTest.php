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

        self::assertEmpty($attributes);
    }

    public function testUpdateFilterableAttributes(): void
    {
        $expectedAttributes = ['title'];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateFilterableAttributes($expectedAttributes);

        $this->assertIsValidPromise($promise);
        $index->waitForTask($promise['taskUid']);

        $filterableAttributes = $index->getFilterableAttributes();

        self::assertSame($expectedAttributes, $filterableAttributes);
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
        self::assertEmpty($filterableAttributes);
    }

    public function testUpdateGranularFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $expectedAttributes = [
            [
                'attributePatterns' => ['title'],
                'features' => [
                    'facetSearch' => true,
                    'filter' => [
                        'equality' => true,
                        'comparison' => false,
                    ],
                ],
            ],
        ];

        $promise = $index->updateFilterableAttributes($expectedAttributes);
        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $filterableAttributes = $index->getFilterableAttributes();
        self::assertSame($expectedAttributes, $filterableAttributes);
    }

    public function testUpdateGeoWithGranularFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateFilterableAttributes([
            [
                'attributePatterns' => ['_geo'],
            ],
        ]);
        $this->assertIsValidPromise($promise);

        $index->waitForTask($promise['taskUid']);
        $filterableAttributes = $index->getFilterableAttributes();
        self::assertSame([
            [
                'attributePatterns' => ['_geo'],
                'features' => [
                    'facetSearch' => false,
                    'filter' => [
                        'equality' => true,
                        'comparison' => false,
                    ],
                ],
            ],
        ], $filterableAttributes);
    }
}
