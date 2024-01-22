<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\KeysQuery;
use PHPUnit\Framework\TestCase;

class KeysQueryTest extends TestCase
{
    public function testToArrayWithSetOffsetAndSetLimit(): void
    {
        $data = (new KeysQuery())->setLimit(10)->setOffset(18);

        self::assertEquals(['limit' => 10, 'offset' => 18], $data->toArray());
    }

    public function testToArrayWithSetOffset(): void
    {
        $data = (new KeysQuery())->setOffset(5);

        self::assertEquals(['offset' => 5], $data->toArray());
    }

    public function testToArrayWithoutSet(): void
    {
        $data = new KeysQuery();

        self::assertEmpty($data->toArray());
    }

    public function testToArrayWithZeros(): void
    {
        $data = (new KeysQuery())->setLimit(0)->setOffset(0);

        self::assertEquals(['limit' => 0, 'offset' => 0], $data->toArray());
    }
}
