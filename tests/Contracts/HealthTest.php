<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\Health;
use Meilisearch\Contracts\HealthStatus;
use PHPUnit\Framework\TestCase;

final class HealthTest extends TestCase
{
    public function testConstruct(): void
    {
        $health = new Health(status: HealthStatus::Available);

        self::assertSame(HealthStatus::Available, $health->getStatus());
    }

    public function testFromArray(): void
    {
        $health = Health::fromArray([
            'status' => 'available',
        ]);

        self::assertSame(HealthStatus::Available, $health->getStatus());
    }

    public function testFromArrayWithUnknownStatus(): void
    {
        $health = Health::fromArray([
            'status' => 'future-status',
        ]);

        self::assertSame(HealthStatus::Unknown, $health->getStatus());
    }
}
