<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\IndexesQuery;
use PHPUnit\Framework\TestCase;

final class IndexesQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new IndexesQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetLimit(): void
    {
        $data = (new IndexesQuery())->setLimit(10);

        self::assertSame(['limit' => 10], $data->toArray());
    }

    public function testSetOffset(): void
    {
        $data = (new IndexesQuery())->setOffset(5);

        self::assertSame(['offset' => 5], $data->toArray());
    }
}
