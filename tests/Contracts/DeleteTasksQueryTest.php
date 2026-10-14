<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\DeleteTasksQuery;
use PHPUnit\Framework\TestCase;

final class DeleteTasksQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new DeleteTasksQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetTypes(): void
    {
        $data = (new DeleteTasksQuery())->setTypes(['abc', 'xyz']);

        self::assertSame(['types' => 'abc,xyz'], $data->toArray());
    }

    public function testSetStatuses(): void
    {
        $data = (new DeleteTasksQuery())->setStatuses(['failed', 'canceled']);

        self::assertSame(['statuses' => 'failed,canceled'], $data->toArray());
    }

    public function testSetIndexUids(): void
    {
        $data = (new DeleteTasksQuery())->setIndexUids(['uid1', 'uid2']);

        self::assertSame(['indexUids' => 'uid1,uid2'], $data->toArray());
    }

    public function testSetUids(): void
    {
        $data = (new DeleteTasksQuery())->setUids([1, 2]);

        self::assertSame(['uids' => '1,2'], $data->toArray());
    }

    public function testSetBeforeEnqueuedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new DeleteTasksQuery())->setBeforeEnqueuedAt($date);

        self::assertSame(['beforeEnqueuedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetAfterEnqueuedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new DeleteTasksQuery())->setAfterEnqueuedAt($date);

        self::assertSame(['afterEnqueuedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetBeforeStartedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new DeleteTasksQuery())->setBeforeStartedAt($date);

        self::assertSame(['beforeStartedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetAfterStartedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new DeleteTasksQuery())->setAfterStartedAt($date);

        self::assertSame(['afterStartedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetBeforeFinishedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new DeleteTasksQuery())->setBeforeFinishedAt($date);

        self::assertSame(['beforeFinishedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetAfterFinishedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new DeleteTasksQuery())->setAfterFinishedAt($date);

        self::assertSame(['afterFinishedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    private static function rfc3339Micro(\DateTimeInterface $date): string
    {
        return \DateTimeImmutable::createFromInterface($date)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s.u\Z');
    }
}
