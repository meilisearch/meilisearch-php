<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Contracts\TaskType;
use Tests\TestCase;

final class DumpTest extends TestCase
{
    public function testCreateDump(): void
    {
        $task = $this->client->createDump();

        self::assertSame(TaskType::DumpCreation, $task->getType());
    }
}
