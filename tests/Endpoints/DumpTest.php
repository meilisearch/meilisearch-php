<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

final class DumpTest extends TestCase
{
    public function testCreateDump(): void
    {
        $dump = $this->client->createDump();

        $this->assertArrayHasKey('uid', $dump);
        $this->assertArrayHasKey('status', $dump);
        $this->assertEquals('processing', $dump['status']);
    }

    public function testGetDumpStatus(): void
    {
        $newDump = $this->client->createDump();
        $dump = $this->client->getDumpStatus($newDump['uid']);

        $this->assertArrayHasKey('uid', $dump);
        $this->assertArrayHasKey('status', $dump);
    }

    public function testDumpNotFound(): void
    {
        $this->expectException(HTTPRequestException::class);

        $this->client->getDumpStatus('not-found');
    }
}
