<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class FilterableAttributesTest extends TestCase
{
    public function testGetDefaultFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        self::assertEmpty($index->getFilterableAttributes());
    }

    public function testUpdateFilterableAttributes(): void
    {
        $expectedAttributes = ['title'];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $task = $index->updateFilterableAttributes($expectedAttributes);
        $index->waitForTask($task['taskUid']);

        self::assertSame($expectedAttributes, $index->getFilterableAttributes());
    }

    public function testResetFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());
        $newAttributes = ['title'];

        $task = $index->updateFilterableAttributes($newAttributes);
        $index->waitForTask($task['taskUid']);

        $task = $index->resetFilterableAttributes();
        $index->waitForTask($task['taskUid']);

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

        $task = $index->updateFilterableAttributes($expectedAttributes);
        $index->waitForTask($task['taskUid']);

        self::assertSame($expectedAttributes, $index->getFilterableAttributes());
    }

    public function testUpdateGeoWithGranularFilterableAttributes(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName());

        $task = $index->updateFilterableAttributes([
            [
                'attributePatterns' => ['_geo'],
            ],
        ]);

        $index->waitForTask($task['taskUid']);

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
