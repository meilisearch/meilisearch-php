<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

use Meilisearch\Contracts\CreateWebhookQuery;
use Meilisearch\Contracts\UpdateWebhookQuery;
use Meilisearch\Contracts\Webhook;
use Meilisearch\Contracts\WebhooksResults;
use Meilisearch\Endpoints\Webhooks;

trait HandlesWebhooks
{
    protected Webhooks $webhooks;

    /**
     * List all webhooks.
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/list-webhooks
     */
    public function getWebhooks(): WebhooksResults
    {
        return $this->webhooks->all();
    }

    /**
     * Get a single webhook by its uuid.
     *
     * @param non-empty-string $uuid
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/get-webhook
     */
    public function getWebhook(string $uuid): Webhook
    {
        return $this->webhooks->get($uuid);
    }

    /**
     * Create a webhook.
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/create-webhook
     */
    public function createWebhook(CreateWebhookQuery $request): Webhook
    {
        return $this->webhooks->create($request);
    }

    /**
     * Update a webhook. Only the fields set on the query are sent.
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/update-webhook
     */
    public function updateWebhook(UpdateWebhookQuery $request): Webhook
    {
        return $this->webhooks->update($request);
    }

    /**
     * Delete a webhook by its uuid.
     *
     * @param non-empty-string $uuid
     *
     * @since Meilisearch v1.17.0
     * @see https://www.meilisearch.com/docs/reference/api/webhooks/delete-webhook
     */
    public function deleteWebhook(string $uuid): void
    {
        $this->webhooks->delete($uuid);
    }
}
