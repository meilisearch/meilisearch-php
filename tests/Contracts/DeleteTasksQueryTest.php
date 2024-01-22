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

        self::assertSame(['types' => 'abc,xyz'], $data->toArray());
    }

    public function testSetAnyDateFilter(): void
    {
        $date = new \DateTime();
        $data = (new DeleteTasksQuery())->setCanceledBy([null])->setBeforeEnqueuedAt($date);

        self::assertSame(['beforeEnqueuedAt' => $date->format(\DateTime::RFC3339)], $data->toArray());
    }

    public function testToArrayWithDifferentSets(): void
    {
        $data = (new DeleteTasksQuery())->setCanceledBy([1, 2])->setStatuses(['enqueued']);

        self::assertSame([
            'statuses' => 'enqueued', 'canceledBy' => '1,2',
        ], $data->toArray()
        );
    }
}
