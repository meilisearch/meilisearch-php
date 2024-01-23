<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\IndexesQuery;
use PHPUnit\Framework\TestCase;

class IndexesQueryTest extends TestCase
{
    public function testToArrayWithSetOffsetAndSetLimit(): void
    {
        $data = (new IndexesQuery())->setLimit(10)->setOffset(18);

        self::assertSame(['offset' => 18, 'limit' => 10], $data->toArray());
    }

    public function testToArrayWithSetOffset(): void
    {
        $data = (new IndexesQuery())->setOffset(5);

        self::assertSame(['offset' => 5], $data->toArray());
    }

    public function testToArrayWithoutSet(): void
    {
        $data = new IndexesQuery();

        self::assertEmpty($data->toArray());
    }

    public function testToArrayWithZeros(): void
    {
        $data = (new IndexesQuery())->setLimit(0)->setOffset(0);

        self::assertSame(['offset' => 0, 'limit' => 0], $data->toArray());
    }
}
