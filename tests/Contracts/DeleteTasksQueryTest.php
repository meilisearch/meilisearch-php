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

        $this->assertEquals($data->toArray(), ['types' => 'abc,xyz']);
    }

    public function testSetAnyDateFilter(): void
    {
        $date = new \DateTime();
        $data = (new DeleteTasksQuery())->setCanceledBy([null])->setBeforeEnqueuedAt($date);

        $this->assertEquals($data->toArray(), ['beforeEnqueuedAt' => $date->format(\DateTime::RFC3339)]);
    }

    public function testToArrayWithDifferentSets(): void
    {
        $data = (new DeleteTasksQuery())->setCanceledBy([1, 2])->setStatuses(['enqueued']);

        $this->assertEquals($data->toArray(), [
            'canceledBy' => '1,2', 'statuses' => 'enqueued',
        ]);
    }
}
