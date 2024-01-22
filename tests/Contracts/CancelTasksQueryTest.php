<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\CancelTasksQuery;
use PHPUnit\Framework\TestCase;

class CancelTasksQueryTest extends TestCase
{
    public function testSetTypes(): void
    {
        $data = (new CancelTasksQuery())->setTypes(['abc', 'xyz']);

        self::assertEquals(['types' => 'abc,xyz'], $data->toArray());
    }

    public function testSetAnyDateFilter(): void
    {
        $date = new \DateTime();
        $data = (new CancelTasksQuery())->setBeforeEnqueuedAt($date);

        self::assertEquals(['beforeEnqueuedAt' => $date->format(\DateTime::RFC3339)], $data->toArray());
    }

    public function testToArrayWithDifferentSets(): void
    {
        $data = (new CancelTasksQuery())->setUids([1, 2, 3])->setStatuses(['enqueued']);

        self::assertEquals([
                'uids' => '1,2,3', 'statuses' => 'enqueued',
            ],
            $data->toArray()
        );
    }
}
