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
            'priority' => 1,
            'active' => true,
            'conditions' => [
                [
                    'scope' => 'query',
                    'contains' => 'movie',
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
        self::assertSame(1, $rule->getPriority());
        self::assertTrue($rule->isActive());
        self::assertSame($raw['conditions'], $rule->getConditions());
        self::assertSame($raw['actions'], $rule->getActions());
        self::assertSame($raw, $rule->getRaw());
        self::assertSame($raw, $rule->toArray());
    }
}
