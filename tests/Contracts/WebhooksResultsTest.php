<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\Webhook;
use Meilisearch\Contracts\WebhooksResults;
use PHPUnit\Framework\TestCase;

final class WebhooksResultsTest extends TestCase
{
    public function testCreate(): void
    {
        $firstWebhook = Webhook::fromArray([
            'uuid' => '0b7ef0c6-0f4c-4d5e-9b1e-1f2a3b4c5d6e',
            'isEditable' => true,
            'url' => 'https://example.com/first',
            'headers' => ['referer' => 'https://example.com'],
        ]);
        $secondWebhook = Webhook::fromArray([
            'uuid' => '747feec2-9166-45c3-9341-65c421c5d8ec',
            'isEditable' => false,
            'url' => 'https://example.com/second',
            'headers' => [],
        ]);

        $results = new WebhooksResults(['results' => [$firstWebhook, $secondWebhook]]);

        self::assertCount(2, $results);
        self::assertSame([$firstWebhook, $secondWebhook], $results->getResults());
    }

    public function testCreateWithoutResults(): void
    {
        $results = new WebhooksResults(['results' => []]);

        self::assertCount(0, $results);
        self::assertSame([], $results->getResults());
        self::assertSame(['results' => []], $results->toArray());
    }

    public function testToArray(): void
    {
        $results = new WebhooksResults([
            'results' => [
                Webhook::fromArray([
                    'uuid' => '747feec2-9166-45c3-9341-65c421c5d8ec',
                    'isEditable' => true,
                    'url' => 'https://example.com/hook',
                    'headers' => ['authorization' => 'SEXXXX...'],
                ]),
            ],
        ]);

        self::assertSame([
            'results' => [
                [
                    'uuid' => '747feec2-9166-45c3-9341-65c421c5d8ec',
                    'url' => 'https://example.com/hook',
                    'headers' => ['authorization' => 'SEXXXX...'],
                    'isEditable' => true,
                ],
            ],
        ], $results->toArray());
    }
}
