<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\UpdateWebhookQuery;
use PHPUnit\Framework\TestCase;

final class UpdateWebhookQueryTest extends TestCase
{
    public function testToArrayWithNothingSet(): void
    {
        $query = new UpdateWebhookQuery('747feec2-9166-45c3-9341-65c421c5d8ec');

        self::assertSame('747feec2-9166-45c3-9341-65c421c5d8ec', $query->uuid);
        self::assertSame([], $query->toArray());
    }

    public function testToArrayWithUrl(): void
    {
        $query = (new UpdateWebhookQuery('747feec2-9166-45c3-9341-65c421c5d8ec'))
            ->setUrl('https://example.com/new-hook');

        self::assertSame(['url' => 'https://example.com/new-hook'], $query->toArray());
    }

    public function testToArrayWithHeaderRemoval(): void
    {
        $query = (new UpdateWebhookQuery('747feec2-9166-45c3-9341-65c421c5d8ec'))
            ->setHeaders(['referer' => null]);

        self::assertSame(['headers' => ['referer' => null]], $query->toArray());
    }

    public function testToArrayWithNullHeaders(): void
    {
        $query = (new UpdateWebhookQuery('747feec2-9166-45c3-9341-65c421c5d8ec'))
            ->setHeaders(null);

        self::assertSame(['headers' => null], $query->toArray());
    }

    public function testToArrayWithEmptyHeaders(): void
    {
        $query = (new UpdateWebhookQuery('747feec2-9166-45c3-9341-65c421c5d8ec'))
            ->setHeaders([]);

        self::assertSame([], $query->toArray());
    }

    public function testToArrayWithUrlAndHeaders(): void
    {
        $query = (new UpdateWebhookQuery('747feec2-9166-45c3-9341-65c421c5d8ec'))
            ->setUrl('https://example.com/new-hook')
            ->setHeaders(['x-new' => 'v']);

        self::assertSame(['url' => 'https://example.com/new-hook', 'headers' => ['x-new' => 'v']], $query->toArray());
    }
}
