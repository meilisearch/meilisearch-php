<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

final class DumpTest extends TestCase
{
    public function testCreateDumpAndGetStatus(): void
    {
        $dump = $this->client->createDump();

        $this->assertArrayHasKey('uid', $dump);
        $this->assertArrayHasKey('status', $dump);
        $this->assertEquals('processing', $dump['status']);

        $dump = $this->client->getDumpStatus($dump['uid']);

        $this->assertArrayHasKey('uid', $dump);
        $this->assertArrayHasKey('status', $dump);
    }

    public function testDumpNotFound(): void
    {
        $this->expectException(HTTPRequestException::class);

        $this->client->getDumpStatus('not-found');
    }
}
