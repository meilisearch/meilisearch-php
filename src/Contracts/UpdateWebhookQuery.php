<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @since Meilisearch v1.17.0
 */
final class UpdateWebhookQuery
{
    /**
     * @var non-empty-string|null
     */
    private ?string $url = null;

    private bool $hasHeaders = false;

    /**
     * @var array<non-empty-string, string|null>|null
     */
    private ?array $headers = null;

    /**
     * @param non-empty-string $uuid
     */
    public function __construct(public readonly string $uuid)
    {
    }

    /**
     * @param non-empty-string $url the URL Meilisearch notifies when a task finishes
     *
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Headers are merged into the existing ones: a null value removes that header,
     * and passing null instead of a map removes all headers.
     *
     * @param array<non-empty-string, string|null>|null $headers
     *
     * @return $this
     */
    public function setHeaders(?array $headers): self
    {
        $this->headers = $headers;
        $this->hasHeaders = true;

        return $this;
    }

    /**
     * Builds the PATCH body from the fields that were set. An empty headers map is omitted
     * because it would leave the existing headers unchanged anyway.
     *
     * @return array{
     *     url?: non-empty-string,
     *     headers?: non-empty-array<non-empty-string, string|null>|null
     * }
     */
    public function toArray(): array
    {
        $payload = [];

        if (null !== $this->url) {
            $payload['url'] = $this->url;
        }

        if ($this->hasHeaders && [] !== $this->headers) {
            $payload['headers'] = $this->headers;
        }

        return $payload;
    }
}
