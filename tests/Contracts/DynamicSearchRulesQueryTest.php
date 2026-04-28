<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\DynamicSearchRulesFilter;
use Meilisearch\Contracts\DynamicSearchRulesQuery;
use PHPUnit\Framework\TestCase;

final class DynamicSearchRulesQueryTest extends TestCase
{
    public function testEmptyQuery(): void
    {
        $data = new DynamicSearchRulesQuery();

        self::assertSame([], $data->toArray());
    }

    public function testSetOffsetAndLimit(): void
    {
        $data = (new DynamicSearchRulesQuery())
            ->setOffset(5)
            ->setLimit(10);

        self::assertSame([
            'offset' => 5,
            'limit' => 10,
        ], $data->toArray());
    }

    public function testSetFilter(): void
    {
        $data = (new DynamicSearchRulesQuery())
            ->setFilter(
                (new DynamicSearchRulesFilter())
                    ->setAttributePatterns(['movie-rule', 'promo*'])
                    ->setActive(true)
            );

        self::assertSame([
            'filter' => [
                'attributePatterns' => ['movie-rule', 'promo*'],
                'active' => true,
            ],
        ], $data->toArray());
    }
}
