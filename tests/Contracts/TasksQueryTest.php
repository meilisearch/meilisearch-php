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

        $this->assertEquals(['types' => 'abc,xyz'], $data->toArray());
    }

    public function testSetAnyDateFilter(): void
    {
        $date = new \DateTime();
        $data = (new TasksQuery())->setBeforeEnqueuedAt($date);

        $this->assertEquals($data->toArray(), ['beforeEnqueuedAt' => $date->format(\DateTime::RFC3339)]);
    }

    public function testToArrayWithSetLimit(): void
    {
        $data = (new TasksQuery())->setLimit(10);

        $this->assertEquals(['limit' => 10], $data->toArray());
    }

    public function testToArrayWithSetLimitWithZero(): void
    {
        $data = (new TasksQuery())->setLimit(0);

        $this->assertEquals(['limit' => 0], $data->toArray());
    }

    public function testToArrayWithDifferentSets(): void
    {
        $data = (new TasksQuery())->setFrom(10)->setLimit(9)->setCanceledBy([1, 4])->setStatuses(['enqueued']);

        $this->assertEquals([
            'limit' => 9, 'from' => 10, 'statuses' => 'enqueued', 'canceledBy' => '1,4',
        ], $data->toArray());
    }
}
