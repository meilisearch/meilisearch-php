<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\CreateWebhookQuery;
use PHPUnit\Framework\TestCase;

final class CreateWebhookQueryTest extends TestCase
{
    public function testToArrayWithHeaders(): void
    {
        $query = new CreateWebhookQuery(
            url: 'https://example.com/hook',
            headers: [
                'authorization' => 'SECURITY_KEY',
                'referer' => 'https://example.com',
            ],
        );

        self::assertSame([
            'url' => 'https://example.com/hook',
            'headers' => [
                'authorization' => 'SECURITY_KEY',
                'referer' => 'https://example.com',
            ],
        ], $query->toArray());
    }

    public function testToArrayOmitsEmptyHeaders(): void
    {
        $query = new CreateWebhookQuery(url: 'https://example.com/hook');

        self::assertSame(['url' => 'https://example.com/hook'], $query->toArray());
    }
}
