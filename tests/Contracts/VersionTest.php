<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\Version;
use PHPUnit\Framework\TestCase;

final class VersionTest extends TestCase
{
    public function testConstruct(): void
    {
        $version = new Version(
            commitSha: 'ea70a7d1c90b4d87c1c3319e9bf280dc790f7f5e',
            commitDate: $date = new \DateTimeImmutable('2025-11-15 10:03:15.000000'),
            pkgVersion: '1.26.0',
        );

        self::assertSame('ea70a7d1c90b4d87c1c3319e9bf280dc790f7f5e', $version->getCommitSha());
        self::assertSame($date, $version->getCommitDate());
        self::assertSame('1.26.0', $version->getPkgVersion());
    }

    public function testFromArray(): void
    {
        $version = Version::fromArray([
            'commitSha' => 'ea70a7d1c90b4d87c1c3319e9bf280dc790f7f5e',
            'commitDate' => '2025-11-15T10:03:15.000000000Z',
            'pkgVersion' => '1.26.0',
        ]);

        self::assertSame('ea70a7d1c90b4d87c1c3319e9bf280dc790f7f5e', $version->getCommitSha());
        self::assertEquals(new \DateTimeImmutable('2025-11-15 10:03:15.000000'), $version->getCommitDate());
        self::assertSame('1.26.0', $version->getPkgVersion());
    }

    public function testFromArrayWithUnknownCommitDate(): void
    {
        $version = Version::fromArray([
            'commitSha' => 'ea70a7d1c90b4d87c1c3319e9bf280dc790f7f5e',
            'commitDate' => 'unknown',
            'pkgVersion' => '1.26.0',
        ]);

        self::assertSame('ea70a7d1c90b4d87c1c3319e9bf280dc790f7f5e', $version->getCommitSha());
        self::assertNull($version->getCommitDate());
        self::assertSame('1.26.0', $version->getPkgVersion());
    }
}
