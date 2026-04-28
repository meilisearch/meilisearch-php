<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\UpdateDynamicSearchRuleQuery;
use PHPUnit\Framework\TestCase;

final class UpdateDynamicSearchRuleQueryTest extends TestCase
{
    public function testActionOnlyPayload(): void
    {
        $data = (new UpdateDynamicSearchRuleQuery('movie-rule'))
            ->setActions([
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
            ]);

        self::assertSame('movie-rule', $data->uid);
        self::assertSame([
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
        ], $data->toArray());
    }

    public function testNullableFieldsCanBeExplicitlySetToNull(): void
    {
        $data = (new UpdateDynamicSearchRuleQuery('movie-rule'))
            ->setDescription(null)
            ->setPriority(null);

        self::assertSame([
            'description' => null,
            'priority' => null,
        ], $data->toArray());
    }

    public function testFullPayload(): void
    {
        $data = (new UpdateDynamicSearchRuleQuery('movie-rule'))
            ->setDescription('Movie promotion')
            ->setPriority(2)
            ->setActive(true)
            ->setConditions([
                [
                    'scope' => 'query',
                    'contains' => 'movie',
                ],
            ])
            ->setActions([
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
            ]);

        self::assertSame([
            'description' => 'Movie promotion',
            'priority' => 2,
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
        ], $data->toArray());
    }
}
