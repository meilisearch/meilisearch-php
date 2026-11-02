<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\CreateWebhookQuery;
use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\UpdateWebhookQuery;
use Meilisearch\Contracts\Webhook;
use Meilisearch\Contracts\WebhooksResults;

/**
 * @phpstan-import-type RawWebhook from Webhook
 *
 * @phpstan-type RawWebhooks array{results: list<RawWebhook>}
 */
final class Webhooks extends Endpoint
{
    protected const PATH = '/webhooks';

    /**
     * List all webhooks.
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/list-webhooks
     */
    public function all(): WebhooksResults
    {
        /** @var RawWebhooks $response */
        $response = $this->http->get(self::PATH);

        return new WebhooksResults([
            'results' => array_map(static fn (array $data): Webhook => Webhook::fromArray($data), $response['results']),
        ]);
    }

    /**
     * Get a single webhook by its uuid.
     *
     * @param non-empty-string $uuid
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/get-webhook
     */
    public function get(string $uuid): Webhook
    {
        $response = $this->http->get(self::PATH.'/'.$uuid);

        return Webhook::fromArray($response);
    }

    /**
     * Create a webhook.
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/create-webhook
     */
    public function create(CreateWebhookQuery $request): Webhook
    {
        $response = $this->http->post(self::PATH, (object) $request->toArray());

        return Webhook::fromArray($response);
    }

    /**
     * Update a webhook. Only the fields set on the query are sent.
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/update-webhook
     */
    public function update(UpdateWebhookQuery $request): Webhook
    {
        $response = $this->http->patch(self::PATH.'/'.$request->uuid, (object) $request->toArray());

        return Webhook::fromArray($response);
    }

    /**
     * Delete a webhook by its uuid.
     *
     * @param non-empty-string $uuid
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/delete-webhook
     */
    public function delete(string $uuid): void
    {
        $this->http->delete(self::PATH.'/'.$uuid);
    }
}
