<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\KeysQuery;
use PHPUnit\Framework\TestCase;

final class KeysQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new KeysQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetLimit(): void
    {
        $data = (new KeysQuery())->setLimit(10);

        self::assertSame(['limit' => 10], $data->toArray());
    }

    public function testSetOffset(): void
    {
        $data = (new KeysQuery())->setOffset(5);

        self::assertSame(['offset' => 5], $data->toArray());
    }
}
