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

        $this->assertEquals(['types' => 'abc,xyz'], $data->toArray());
    }

    public function testSetAnyDateFilter(): void
    {
        $date = new \DateTime();
        $data = (new CancelTasksQuery())->setBeforeEnqueuedAt($date);

        $this->assertEquals(['beforeEnqueuedAt' => $date->format(\DateTime::RFC3339)], $data->toArray());
    }

    public function testToArrayWithDifferentSets(): void
    {
        $data = (new CancelTasksQuery())->setUids([1, 2, 3])->setStatuses(['enqueued']);

        $this->assertEquals([
                'uids' => '1,2,3', 'statuses' => 'enqueued',
            ],
            $data->toArray()
        );
    }
}
