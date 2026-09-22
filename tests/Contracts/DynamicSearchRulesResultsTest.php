<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\DynamicSearchRule;
use Meilisearch\Contracts\DynamicSearchRulesResults;
use PHPUnit\Framework\TestCase;

final class DynamicSearchRulesResultsTest extends TestCase
{
    public function testCreate(): void
    {
        $firstRule = DynamicSearchRule::fromArray([
            'uid' => 'movie-rule',
            'actions' => [
                'pin' => [
                    ['id' => '1', 'position' => 1, 'indexUid' => 'movies'],
                ],
            ],
        ]);
        $secondRule = DynamicSearchRule::fromArray([
            'uid' => 'book-rule',
            'actions' => [
                'pin' => [
                    ['id' => '2', 'position' => 2, 'indexUid' => 'books'],
                ],
            ],
        ]);

        $results = new DynamicSearchRulesResults([
            'results' => [$firstRule, $secondRule],
            'offset' => 1,
            'limit' => 2,
            'total' => 5,
        ]);

        self::assertCount(2, $results);
        self::assertSame(1, $results->getOffset());
        self::assertSame(2, $results->getLimit());
        self::assertSame(5, $results->getTotal());
        self::assertSame('movie-rule', $results->getResults()[0]->getUid());
        self::assertSame('book-rule', $results[1]->getUid());

        $array = $results->toArray();
        self::assertSame(1, $array['offset']);
        self::assertSame(2, $array['limit']);
        self::assertSame(5, $array['total']);
        self::assertCount(2, $array['results']);
    }
}
