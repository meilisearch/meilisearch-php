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

        $this->assertEquals($data->toArray(), ['types' => 'abc,xyz']);
    }

    public function testSetAnyDateFilter(): void
    {
        $date = new \DateTime();
        $data = (new CancelTasksQuery())->setBeforeEnqueuedAt($date);

        $this->assertEquals($data->toArray(), ['beforeEnqueuedAt' => $date->format(\DateTime::RFC3339)]);
    }

    public function testToArrayWithDifferentSets(): void
    {
        $data = (new CancelTasksQuery())->setCanceledBy([1, 2, 3])->setStatuses(['enqueued']);

        $this->assertEquals($data->toArray(), [
            'canceledBy' => '1,2,3', 'statuses' => 'enqueued',
        ]);
    }
}
