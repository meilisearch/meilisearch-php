<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\DynamicSearchRule;
use PHPUnit\Framework\TestCase;

final class DynamicSearchRuleTest extends TestCase
{
    public function testFromArray(): void
    {
        $raw = [
            'uid' => 'movie-rule',
            'description' => 'Movie promotion',
            'lastUpdatedAt' => '2026-07-27T06:47:12.123456789Z',
            'precedence' => 1,
            'active' => true,
            'conditions' => [
                'query' => [
                    'isEmpty' => false,
                    'words' => 'movie',
                ],
                'time' => [
                    'start' => '2026-01-01T00:00:00Z',
                    'end' => null,
                ],
                'filter' => [
                    'values' => [
                        'color' => 'red',
                        'category' => 'shirt',
                    ],
                ],
            ],
            'actions' => [
                [
                    'selector' => [
                        'indexUid' => 'movies',
                        'id' => '1',
                    ],
                    'action' => [
                        'type' => 'pin',
                        'position' => 1,
                    ],
                ],
            ],
        ];

        $rule = DynamicSearchRule::fromArray($raw);

        self::assertSame('movie-rule', $rule->getUid());
        self::assertSame('Movie promotion', $rule->getDescription());
        self::assertSame(
            '2026-07-27T06:47:12.123456+00:00',
            $rule->getLastUpdatedAt()?->format('Y-m-d\TH:i:s.uP')
        );
        self::assertSame(1, $rule->getPrecedence());
        self::assertTrue($rule->isActive());
        self::assertSame($raw['conditions'], $rule->getConditions());
        self::assertSame($raw['actions'], $rule->getActions());
        self::assertSame($raw, $rule->getRaw());
        self::assertSame($raw, $rule->toArray());
    }

    public function testLastUpdatedAtIsOptionalForOlderResponses(): void
    {
        $rule = DynamicSearchRule::fromArray([
            'uid' => 'movie-rule',
            'actions' => [],
        ]);

        self::assertNull($rule->getLastUpdatedAt());
    }
}
