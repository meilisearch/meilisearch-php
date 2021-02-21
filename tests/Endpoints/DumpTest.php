<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class DumpTest extends TestCase
{
    public function testCreateDumpAndGetStatus(): void
    {
        $dump = $this->client->createDump();

        $this->assertArrayHasKey('uid', $dump);
        $this->assertArrayHasKey('status', $dump);
        $this->assertEquals('in_progress', $dump['status']);

        $dump = $this->client->getDumpStatus($dump['uid']);

        $this->assertArrayHasKey('uid', $dump);
        $this->assertArrayHasKey('status', $dump);
    }

    public function testDumpNotFound(): void
    {
        $this->expectException(ApiException::class);

        $this->client->getDumpStatus('not-found');
    }
}
