<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\CancelTasksQuery;
use PHPUnit\Framework\TestCase;

final class CancelTasksQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new CancelTasksQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetTypes(): void
    {
        $data = (new CancelTasksQuery())->setTypes(['abc', 'xyz']);

        self::assertSame(['types' => 'abc,xyz'], $data->toArray());
    }

    public function testSetStatuses(): void
    {
        $data = (new CancelTasksQuery())->setStatuses(['failed', 'canceled']);

        self::assertSame(['statuses' => 'failed,canceled'], $data->toArray());
    }

    public function testSetIndexUids(): void
    {
        $data = (new CancelTasksQuery())->setIndexUids(['uid1', 'uid2']);

        self::assertSame(['indexUids' => 'uid1,uid2'], $data->toArray());
    }

    public function testSetUids(): void
    {
        $data = (new CancelTasksQuery())->setUids([1, 2]);

        self::assertSame(['uids' => '1,2'], $data->toArray());
    }

    public function testSetBeforeEnqueuedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new CancelTasksQuery())->setBeforeEnqueuedAt($date);

        self::assertSame(['beforeEnqueuedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetAfterEnqueuedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new CancelTasksQuery())->setAfterEnqueuedAt($date);

        self::assertSame(['afterEnqueuedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetBeforeStartedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new CancelTasksQuery())->setBeforeStartedAt($date);

        self::assertSame(['beforeStartedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetAfterStartedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new CancelTasksQuery())->setAfterStartedAt($date);

        self::assertSame(['afterStartedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetBeforeFinishedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new CancelTasksQuery())->setBeforeFinishedAt($date);

        self::assertSame(['beforeFinishedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    public function testSetAfterFinishedAt(): void
    {
        $date = new \DateTimeImmutable();
        $data = (new CancelTasksQuery())->setAfterFinishedAt($date);

        self::assertSame(['afterFinishedAt' => self::rfc3339Micro($date)], $data->toArray());
    }

    private static function rfc3339Micro(\DateTimeInterface $date): string
    {
        return \DateTimeImmutable::createFromInterface($date)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s.u\Z');
    }
}
