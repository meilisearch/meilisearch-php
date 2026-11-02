<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @since Meilisearch v1.17.0
 */
final class CreateWebhookQuery
{
    /**
     * @param non-empty-string                $url     the URL Meilisearch notifies when a task finishes
     * @param array<non-empty-string, string> $headers headers sent with every webhook request
     */
    public function __construct(
        private readonly string $url,
        private readonly array $headers = [],
    ) {
    }

    /**
     * @return array{
     *     url: non-empty-string,
     *     headers?: array<non-empty-string, string>
     * }
     */
    public function toArray(): array
    {
        $payload = ['url' => $this->url];
        if ([] !== $this->headers) {
            $payload['headers'] = $this->headers;
        }

        return $payload;
    }
}
