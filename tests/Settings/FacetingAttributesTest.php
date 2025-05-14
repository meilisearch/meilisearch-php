<?php

declare(strict_types=1);

namespace Tests\Settings;

use Tests\TestCase;

final class FacetingAttributesTest extends TestCase
{
    public function testGetDefaultFaceting(): void
    {
        $index = $this->createEmptyIndex($this->safeIndexName('books-1'));

        self::assertSame([
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => [
                '*' => 'alpha',
            ],
        ], $index->getFaceting());
    }

    public function testUpdateFacetingAttributes(): void
    {
        $newAttributes = ['sortFacetValuesBy' => ['*' => 'count']];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $task = $index->updateFaceting($newAttributes);
        $index->waitForTask($task->getTaskUid());

        self::assertSame([
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => ['*' => 'count'],
        ], $index->getFaceting());
    }

    public function testResetFaceting(): void
    {
        $newAttributes = ['sortFacetValuesBy' => ['*' => 'count']];
        $index = $this->createEmptyIndex($this->safeIndexName());

        $task = $index->updateFaceting($newAttributes);
        $index->waitForTask($task->getTaskUid());

        $task = $index->resetFaceting();
        $index->waitForTask($task->getTaskUid());

        self::assertSame([
            'maxValuesPerFacet' => 100,
            'sortFacetValuesBy' => ['*' => 'alpha'],
        ], $index->getFaceting());
    }
}
