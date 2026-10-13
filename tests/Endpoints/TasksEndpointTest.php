<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\TasksResults;
use Meilisearch\Contracts\TaskStatus;
use Meilisearch\Endpoints\Tasks;
use PHPUnit\Framework\TestCase;

final class TasksEndpointTest extends TestCase
{
    public function testAllReturnsTasksResults(): void
    {
        $http = $this->createMock(Http::class);
        $http->expects(self::once())
            ->method('get')
            ->with('/tasks/', ['limit' => 20])
            ->willReturn([
                'results' => [[
                    'taskUid' => 7,
                    'indexUid' => 'movies',
                    'status' => 'succeeded',
                    'type' => 'documentAdditionOrUpdate',
                    'enqueuedAt' => '2025-04-09T10:28:12.236789Z',
                ]],
                'from' => 2,
                'limit' => 20,
                'next' => 22,
                'total' => 40,
            ]);

        $results = (new Tasks($http))->all(['limit' => 20]);

        self::assertInstanceOf(TasksResults::class, $results);
        self::assertSame(2, $results->getFrom());
        self::assertSame(20, $results->getLimit());
        self::assertSame(22, $results->getNext());
        self::assertSame(40, $results->getTotal());
        self::assertSame(7, $results->getResults()[0]->getTaskUid());
        self::assertSame(TaskStatus::Succeeded, $results->getResults()[0]->getStatus());
    }
}
