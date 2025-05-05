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

        self::assertEmpty($index->getFilterableAttributes());
    }

    public function testUpdateFilterableAttributes(): void
    {
        $expectedAttributes = ['title'];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateFilterableAttributes($expectedAttributes);
        $index->waitForTask($promise['taskUid']);

        self::assertSame($expectedAttributes, $index->getFilterableAttributes());
    }

    public function testResetFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $newAttributes = ['title'];

        $promise = $index->updateFilterableAttributes($newAttributes);
        $index->waitForTask($promise['taskUid']);

        $promise = $index->resetFilterableAttributes();
        $index->waitForTask($promise['taskUid']);

        self::assertEmpty($index->getFilterableAttributes());
    }

    public function testUpdateGranularFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $expectedAttributes = [
            'author',
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
        $index->waitForTask($promise['taskUid']);

        self::assertSame($expectedAttributes, $index->getFilterableAttributes());
    }

    public function testUpdateGeoWithGranularFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $promise = $index->updateFilterableAttributes([
            [
                'attributePatterns' => ['_geo'],
            ],
        ]);

        $index->waitForTask($promise['taskUid']);

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
        ], $index->getFilterableAttributes());
    }
}
