<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\DeleteTasksQuery;
use PHPUnit\Framework\TestCase;

class DeleteTasksQueryTest extends TestCase
{
    public function testSetTypes(): void
    {
        $data = (new DeleteTasksQuery())->setTypes(['abc', 'xyz']);

        self::assertEquals(['types' => 'abc,xyz'], $data->toArray());
    }

    public function testSetAnyDateFilter(): void
    {
        $date = new \DateTime();
        $data = (new DeleteTasksQuery())->setCanceledBy([null])->setBeforeEnqueuedAt($date);

        self::assertEquals(['beforeEnqueuedAt' => $date->format(\DateTime::RFC3339)], $data->toArray());
    }

    public function testToArrayWithDifferentSets(): void
    {
        $data = (new DeleteTasksQuery())->setCanceledBy([1, 2])->setStatuses(['enqueued']);

        self::assertEquals([
            'canceledBy' => '1,2', 'statuses' => 'enqueued',
        ], $data->toArray()
        );
    }
}
