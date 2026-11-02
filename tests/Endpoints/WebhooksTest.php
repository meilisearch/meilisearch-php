<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use Meilisearch\Contracts\CreateWebhookQuery;
use Meilisearch\Contracts\UpdateWebhookQuery;
use Meilisearch\Contracts\Webhook;
use Meilisearch\Exceptions\ApiException;
use Meilisearch\Http\Client;
use Tests\TestCase;

final class WebhooksTest extends TestCase
{
    private const WEBHOOK_URL = 'http://127.0.0.1:1/hook';
    private const UNKNOWN_WEBHOOK_UUID = '00000000-0000-4000-8000-000000000000';

    private Client $http;

    protected function setUp(): void
    {
        parent::setUp();

        $this->http = new Client($this->host, getenv('MEILISEARCH_API_KEY'));
        $this->deleteEditableWebhooks();
    }

    protected function tearDown(): void
    {
        $this->deleteEditableWebhooks();

        parent::tearDown();
    }

    public function testCreateWebhook(): void
    {
        $webhook = $this->client->createWebhook(new CreateWebhookQuery(
            url: self::WEBHOOK_URL,
            headers: [
                'referer' => 'https://example.com',
                'authorization' => 'SECURITY_KEY',
            ],
        ));

        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $webhook->getUuid());
        self::assertSame(self::WEBHOOK_URL, $webhook->getUrl());
        self::assertTrue($webhook->isEditable());
        self::assertSame('https://example.com', $webhook->getHeaders()['referer']);
        self::assertArrayHasKey('authorization', $webhook->getHeaders());
    }

    public function testCreateWebhookWithoutHeaders(): void
    {
        $webhook = $this->client->createWebhook(new CreateWebhookQuery(url: self::WEBHOOK_URL));

        self::assertSame(self::WEBHOOK_URL, $webhook->getUrl());
        self::assertSame([], $webhook->getHeaders());
    }

    public function testGetWebhook(): void
    {
        $created = $this->client->createWebhook(new CreateWebhookQuery(
            url: self::WEBHOOK_URL,
            headers: ['referer' => 'https://example.com'],
        ));

        $webhook = $this->client->getWebhook($created->getUuid());

        self::assertSame($created->getUuid(), $webhook->getUuid());
        self::assertSame($created->getUrl(), $webhook->getUrl());
        self::assertSame($created->isEditable(), $webhook->isEditable());
        self::assertSame('https://example.com', $webhook->getHeaders()['referer']);
    }

    public function testGetWebhooks(): void
    {
        $preexistingWebhooks = $this->client->getWebhooks();

        $first = $this->client->createWebhook(new CreateWebhookQuery(url: self::WEBHOOK_URL));
        $second = $this->client->createWebhook(new CreateWebhookQuery(
            url: 'http://127.0.0.1:1/other-hook',
            headers: ['referer' => 'https://example.com'],
        ));

        $webhooks = $this->client->getWebhooks();

        self::assertCount(\count($preexistingWebhooks) + 2, $webhooks);
        self::assertContainsOnlyInstancesOf(Webhook::class, $webhooks->getResults());

        $expectedUuids = [...self::webhookUuids($preexistingWebhooks->getResults()), $first->getUuid(), $second->getUuid()];
        $actualUuids = self::webhookUuids($webhooks->getResults());
        sort($expectedUuids);
        sort($actualUuids);
        self::assertSame($expectedUuids, $actualUuids);
    }

    public function testUpdateWebhookUrl(): void
    {
        $created = $this->client->createWebhook(new CreateWebhookQuery(
            url: self::WEBHOOK_URL,
            headers: ['referer' => 'https://example.com'],
        ));

        $webhook = $this->client->updateWebhook(
            (new UpdateWebhookQuery($created->getUuid()))->setUrl('http://127.0.0.1:1/updated-hook'),
        );

        self::assertSame($created->getUuid(), $webhook->getUuid());
        self::assertSame('http://127.0.0.1:1/updated-hook', $webhook->getUrl());
        self::assertSame(['referer' => 'https://example.com'], $webhook->getHeaders());
        self::assertSame('http://127.0.0.1:1/updated-hook', $this->client->getWebhook($created->getUuid())->getUrl());
    }

    public function testUpdateWebhookWithEmptyQuery(): void
    {
        $created = $this->client->createWebhook(new CreateWebhookQuery(
            url: self::WEBHOOK_URL,
            headers: ['referer' => 'https://example.com'],
        ));

        $webhook = $this->client->updateWebhook(new UpdateWebhookQuery($created->getUuid()));

        self::assertSame($created->toArray(), $webhook->toArray());
    }

    public function testUpdateWebhookHeadersMergesNewHeader(): void
    {
        $created = $this->createWebhookWithCustomHeaders();

        $webhook = $this->client->updateWebhook(
            (new UpdateWebhookQuery($created->getUuid()))->setHeaders(['x-new' => 'v']),
        );

        $headers = $webhook->getHeaders();
        ksort($headers);
        self::assertSame(['referer' => 'https://example.com', 'x-custom' => 'custom', 'x-new' => 'v'], $headers);
        self::assertSame(self::WEBHOOK_URL, $webhook->getUrl());
    }

    public function testUpdateWebhookHeadersRemovesSingleHeader(): void
    {
        $created = $this->createWebhookWithCustomHeaders();

        $webhook = $this->client->updateWebhook(
            (new UpdateWebhookQuery($created->getUuid()))->setHeaders(['referer' => null]),
        );

        self::assertSame(['x-custom' => 'custom'], $webhook->getHeaders());
        self::assertSame(['x-custom' => 'custom'], $this->client->getWebhook($created->getUuid())->getHeaders());
    }

    public function testUpdateWebhookHeadersClearsAllHeaders(): void
    {
        $created = $this->createWebhookWithCustomHeaders();

        $webhook = $this->client->updateWebhook(
            (new UpdateWebhookQuery($created->getUuid()))->setHeaders(null),
        );

        self::assertSame([], $webhook->getHeaders());
        self::assertSame([], $this->client->getWebhook($created->getUuid())->getHeaders());
    }

    public function testUpdateWebhookHeadersWithEmptyMapKeepsHeaders(): void
    {
        $created = $this->createWebhookWithCustomHeaders();

        $webhook = $this->client->updateWebhook(
            (new UpdateWebhookQuery($created->getUuid()))->setHeaders([]),
        );

        self::assertSame($created->getHeaders(), $webhook->getHeaders());
    }

    public function testDeleteWebhook(): void
    {
        $created = $this->client->createWebhook(new CreateWebhookQuery(url: self::WEBHOOK_URL));

        $this->client->deleteWebhook($created->getUuid());

        $remainingUuids = self::webhookUuids($this->client->getWebhooks()->getResults());
        self::assertNotContains($created->getUuid(), $remainingUuids);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(404);

        $this->client->getWebhook($created->getUuid());
    }

    public function testWebhookNotFoundOnGet(): void
    {
        $this->assertApiException(
            fn () => $this->client->getWebhook(self::UNKNOWN_WEBHOOK_UUID),
            404,
            'webhook_not_found',
        );
    }

    public function testWebhookNotFoundOnUpdate(): void
    {
        $this->assertApiException(
            fn () => $this->client->updateWebhook((new UpdateWebhookQuery(self::UNKNOWN_WEBHOOK_UUID))->setUrl(self::WEBHOOK_URL)),
            404,
            'webhook_not_found',
        );
    }

    public function testWebhookNotFoundOnDelete(): void
    {
        $this->assertApiException(
            fn () => $this->client->deleteWebhook(self::UNKNOWN_WEBHOOK_UUID),
            404,
            'webhook_not_found',
        );
    }

    public function testInvalidWebhookUuid(): void
    {
        $this->assertApiException(
            fn () => $this->client->getWebhook('not-a-uuid'),
            400,
            'invalid_webhook_uuid',
        );
    }

    private function assertApiException(\Closure $request, int $expectedHttpStatus, string $expectedErrorCode): void
    {
        try {
            $request();
            self::fail('ApiException not raised.');
        } catch (ApiException $e) {
            self::assertSame($expectedHttpStatus, $e->httpStatus);
            self::assertSame($expectedErrorCode, $e->errorCode);
        }
    }

    private function createWebhookWithCustomHeaders(): Webhook
    {
        return $this->client->createWebhook(new CreateWebhookQuery(
            url: self::WEBHOOK_URL,
            headers: [
                'referer' => 'https://example.com',
                'x-custom' => 'custom',
            ],
        ));
    }

    /**
     * @param list<Webhook> $webhooks
     *
     * @return list<non-empty-string>
     */
    private static function webhookUuids(array $webhooks): array
    {
        return array_map(static fn (Webhook $webhook): string => $webhook->getUuid(), $webhooks);
    }

    private function deleteEditableWebhooks(): void
    {
        /** @var array{results: list<array{uuid: non-empty-string, isEditable: bool}>} $response */
        $response = $this->http->get('/webhooks');

        foreach ($response['results'] as $webhook) {
            if ($webhook['isEditable']) {
                $this->http->delete('/webhooks/'.$webhook['uuid']);
            }
        }
    }
}
