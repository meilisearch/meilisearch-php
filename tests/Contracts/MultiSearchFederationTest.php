<?php

declare(strict_types=1);

namespace Contracts;

use Meilisearch\Contracts\MultiSearchFederation;
use PHPUnit\Framework\TestCase;

final class MultiSearchFederationTest extends TestCase
{
    public function testEmptyOptions(): void
    {
        $data = new MultiSearchFederation();

        self::assertSame([], $data->toArray());
    }

    public function testSetLimit(): void
    {
        $data = (new MultiSearchFederation())->setLimit(10);

        self::assertSame(['limit' => 10], $data->toArray());
    }

    public function testSetOffset(): void
    {
        $data = (new MultiSearchFederation())->setOffset(5);

        self::assertSame(['offset' => 5], $data->toArray());
    }
}
