<?php

declare(strict_types=1);

namespace Tests\Contracts;

use MeiliSearch\Contracts\TasksQuery;
use PHPUnit\Framework\TestCase;

class TasksQueryTest extends TestCase
{
    public function testSetTypes(): void
    {
        $data = (new TasksQuery())->setTypes(['abc', 'xyz']);

        $this->assertEquals($data->toArray(), ['type' => 'abc,xyz']);
    }

    public function testSetNext(): void
    {
        $data = (new TasksQuery())->setNext(99);

        $this->assertEquals($data->toArray(), ['next' => 99]);
    }

    public function testToArrayWithSetLimit(): void
    {
        $data = (new TasksQuery())->setLimit(10);

        $this->assertEquals($data->toArray(), ['limit' => 10]);
    }

    public function testToArrayWithSetLimitWithZero(): void
    {
        $data = (new TasksQuery())->setLimit(0);

        $this->assertEquals($data->toArray(), ['limit' => 0]);
    }

    public function testToArrayWithDifferentSets(): void
    {
        $data = (new TasksQuery())->setFrom(10)->setLimit(9)->setNext(99)->setStatus(['enqueued']);

        $this->assertEquals($data->toArray(), [
            'limit' => 9, 'next' => 99, 'from' => 10, 'status' => 'enqueued',
        ]);
    }
}
