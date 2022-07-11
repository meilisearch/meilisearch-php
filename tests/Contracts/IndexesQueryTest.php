<?php

declare(strict_types=1);

namespace Tests\Contracts;

use MeiliSearch\Contracts\IndexesQuery;
use PHPUnit\Framework\TestCase;

class IndexesQueryTest extends TestCase
{
    public function testToArrayWithSetOffsetAndSetLimit(): void
    {
        $data = (new IndexesQuery())->setLimit(10)->setOffset(18);

        $this->assertEquals($data->toArray(), ['limit' => 10, 'offset' => 18]);
    }

    public function testToArrayWithSetOffset(): void
    {
        $data = (new IndexesQuery())->setOffset(5);

        $this->assertEquals($data->toArray(), ['offset' => 5]);
    }

    public function testToArrayWithoutSet(): void
    {
        $data = new IndexesQuery();

        $this->assertEquals($data->toArray(), []);
    }

    public function testToArrayWithZeros(): void
    {
        $data = (new IndexesQuery())->setLimit(0)->setOffset(0);

        $this->assertEquals($data->toArray(), ['limit' => 0, 'offset' => 0]);
    }
}
