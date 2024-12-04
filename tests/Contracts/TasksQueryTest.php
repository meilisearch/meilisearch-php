<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\TasksQuery;
use PHPUnit\Framework\TestCase;

final class TasksQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new TasksQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetTypes(): void
    {
        $data = (new TasksQuery())->setTypes(['abc', 'xyz']);

        self::assertSame(['types' => 'abc,xyz'], $data->toArray());
    }

    public function testSetStatuses(): void
    {
        $data = (new TasksQuery())->setStatuses(['failed', 'canceled']);

        self::assertSame(['statuses' => 'failed,canceled'], $data->toArray());
    }

    public function testSetIndexUids(): void
    {
        $data = (new TasksQuery())->setIndexUids(['uid1', 'uid2']);

        self::assertSame(['indexUids' => 'uid1,uid2'], $data->toArray());
    }

    public function testSetUids(): void
    {
        $data = (new TasksQuery())->setUids(['uid1', 'uid2']);

        self::assertSame(['uids' => 'uid1,uid2'], $data->toArray());
    }

    public function testSetBeforeEnqueuedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new TasksQuery())->setBeforeEnqueuedAt($date);

        self::assertSame(['beforeEnqueuedAt' => $date->format(\DateTimeInterface::RFC3339)], $data->toArray());
    }

    public function testSetAfterEnqueuedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new TasksQuery())->setAfterEnqueuedAt($date);

        self::assertSame(['afterEnqueuedAt' => $date->format(\DateTimeInterface::RFC3339)], $data->toArray());
    }

    public function testSetBeforeStartedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new TasksQuery())->setBeforeStartedAt($date);

        self::assertSame(['beforeStartedAt' => $date->format(\DateTimeInterface::RFC3339)], $data->toArray());
    }

    public function testSetAfterStartedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new TasksQuery())->setAfterStartedAt($date);

        self::assertSame(['afterStartedAt' => $date->format(\DateTimeInterface::RFC3339)], $data->toArray());
    }

    public function testSetBeforeFinishedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new TasksQuery())->setBeforeFinishedAt($date);

        self::assertSame(['beforeFinishedAt' => $date->format(\DateTimeInterface::RFC3339)], $data->toArray());
    }

    public function testSetAfterFinishedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new TasksQuery())->setAfterFinishedAt($date);

        self::assertSame(['afterFinishedAt' => $date->format(\DateTimeInterface::RFC3339)], $data->toArray());
    }

    /**
     * @testWith [0]
     *           [10]
     */
    public function testSetLimit(int $limit): void
    {
        $data = (new TasksQuery())->setLimit($limit);

        self::assertSame(['limit' => $limit], $data->toArray());
    }

    public function testSetFrom(): void
    {
        $data = (new TasksQuery())->setFrom(1);

        self::assertSame(['from' => 1], $data->toArray());
    }

    /**
     * @testWith [true, "true"]
     *           [false, "false"]
     */
    public function testSetReverse(bool $reverse, string $expected): void
    {
        $data = (new TasksQuery())->setReverse($reverse);

        self::assertSame(['reverse' => $expected], $data->toArray());
    }
}
