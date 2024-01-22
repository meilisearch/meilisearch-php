<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\TasksQuery;
use PHPUnit\Framework\TestCase;

class TasksQueryTest extends TestCase
{
    public function testSetTypes(): void
    {
        $data = (new TasksQuery())->setTypes(['abc', 'xyz']);

        self::assertSame(['types' => 'abc,xyz'], $data->toArray());
    }

    public function testSetAnyDateFilter(): void
    {
        $date = new \DateTime();
        $data = (new TasksQuery())->setBeforeEnqueuedAt($date);

        self::assertSame($data->toArray(), ['beforeEnqueuedAt' => $date->format(\DateTime::RFC3339)]);
    }

    public function testToArrayWithSetLimit(): void
    {
        $data = (new TasksQuery())->setLimit(10);

        self::assertSame(['limit' => 10], $data->toArray());
    }

    public function testToArrayWithSetLimitWithZero(): void
    {
        $data = (new TasksQuery())->setLimit(0);

        self::assertSame(['limit' => 0], $data->toArray());
    }

    public function testToArrayWithDifferentSets(): void
    {
        $data = (new TasksQuery())->setFrom(10)->setLimit(9)->setCanceledBy([1, 4])->setStatuses(['enqueued']);

        self::assertSame([
            'statuses' => 'enqueued', 'from' => 10, 'limit' => 9, 'canceledBy' => '1,4',
        ], $data->toArray());
    }
}
