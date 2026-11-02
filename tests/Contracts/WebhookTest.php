<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Meilisearch\Contracts\Webhook;
use PHPUnit\Framework\TestCase;

final class WebhookTest extends TestCase
{
    public function testConstruct(): void
    {
        $webhook = new Webhook(
            uuid: '747feec2-9166-45c3-9341-65c421c5d8ec',
            url: 'https://example.com/hook',
            headers: ['referer' => 'https://example.com'],
            isEditable: true,
        );

        self::assertSame('747feec2-9166-45c3-9341-65c421c5d8ec', $webhook->getUuid());
        self::assertSame('https://example.com/hook', $webhook->getUrl());
        self::assertSame(['referer' => 'https://example.com'], $webhook->getHeaders());
        self::assertTrue($webhook->isEditable());
    }

    public function testFromArray(): void
    {
        $webhook = Webhook::fromArray([
            'uuid' => '747feec2-9166-45c3-9341-65c421c5d8ec',
            'isEditable' => true,
            'url' => 'https://example.com/hook',
            'headers' => [
                'authorization' => 'SEXXXX...',
                'referer' => 'https://example.com',
            ],
        ]);

        self::assertSame('747feec2-9166-45c3-9341-65c421c5d8ec', $webhook->getUuid());
        self::assertSame('https://example.com/hook', $webhook->getUrl());
        self::assertSame([
            'authorization' => 'SEXXXX...',
            'referer' => 'https://example.com',
        ], $webhook->getHeaders());
        self::assertTrue($webhook->isEditable());
    }

    public function testFromArrayWithNonEditableWebhook(): void
    {
        $webhook = Webhook::fromArray([
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'isEditable' => false,
            'url' => 'https://example.com/hook',
            'headers' => ['Authorization' => 'Bearer XXX...'],
        ]);

        self::assertSame('00000000-0000-0000-0000-000000000000', $webhook->getUuid());
        self::assertSame(['Authorization' => 'Bearer XXX...'], $webhook->getHeaders());
        self::assertFalse($webhook->isEditable());
    }

    public function testFromArrayWithEmptyHeaders(): void
    {
        $webhook = Webhook::fromArray([
            'uuid' => '747feec2-9166-45c3-9341-65c421c5d8ec',
            'isEditable' => true,
            'url' => 'https://example.com/hook',
            'headers' => [],
        ]);

        self::assertSame([], $webhook->getHeaders());
    }

    public function testToArray(): void
    {
        $webhook = Webhook::fromArray([
            'uuid' => '747feec2-9166-45c3-9341-65c421c5d8ec',
            'isEditable' => true,
            'url' => 'https://example.com/hook',
            'headers' => [],
        ]);

        self::assertSame([
            'uuid' => '747feec2-9166-45c3-9341-65c421c5d8ec',
            'url' => 'https://example.com/hook',
            'headers' => [],
            'isEditable' => true,
        ], $webhook->toArray());
    }
}
