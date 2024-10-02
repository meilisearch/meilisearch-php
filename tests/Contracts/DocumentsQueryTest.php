<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\DocumentsQuery;
use PHPUnit\Framework\TestCase;

final class DocumentsQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new DocumentsQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetFields(): void
    {
        $data = (new DocumentsQuery())->setLimit(10)->setFields(['abc', 'xyz']);

        self::assertSame(['limit' => 10, 'fields' => 'abc,xyz'], $data->toArray());
    }

    public function testSetLimit(): void
    {
        $data = (new DocumentsQuery())->setLimit(10);

        self::assertSame(['limit' => 10], $data->toArray());
    }

    public function testSetOffset(): void
    {
        $data = (new DocumentsQuery())->setOffset(5);

        self::assertSame(['offset' => 5], $data->toArray());
    }
}
