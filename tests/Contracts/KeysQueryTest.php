<?php

declare(strict_types=1);

namespace Tests\Contracts;

use MeiliSearch\Contracts\KeysQuery;
use PHPUnit\Framework\TestCase;

class KeysQueryTest extends TestCase
{
    public function testToArrayWithSetOffsetAndSetLimit(): void
    {
        $data = (new KeysQuery())->setLimit(10)->setOffset(18);

        $this->assertEquals($data->toArray(), ['limit' => 10, 'offset' => 18]);
    }

    public function testToArrayWithSetOffset(): void
    {
        $data = (new KeysQuery())->setOffset(5);

        $this->assertEquals($data->toArray(), ['offset' => 5]);
    }

    public function testToArrayWithoutSet(): void
    {
        $data = new KeysQuery();

        $this->assertEquals($data->toArray(), []);
    }
}
