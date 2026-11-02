<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type RawWebhook array{
 *     uuid: non-empty-string,
 *     isEditable: bool,
 *     url: non-empty-string,
 *     headers: array<string, string>
 * }
 *
 * @since Meilisearch v1.17.0
 */
final class Webhook
{
    /**
     * @param non-empty-string      $uuid
     * @param non-empty-string      $url
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $uuid,
        private readonly string $url,
        private readonly array $headers,
        private readonly bool $isEditable,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return non-empty-string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Values of the `authorization` header are redacted by Meilisearch.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isEditable(): bool
    {
        return $this->isEditable;
    }

    /**
     * @return array{
     *     uuid: non-empty-string,
     *     url: non-empty-string,
     *     headers: array<string, string>,
     *     isEditable: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'url' => $this->url,
            'headers' => $this->headers,
            'isEditable' => $this->isEditable,
        ];
    }

    /**
     * @param RawWebhook $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            url: $data['url'],
            headers: $data['headers'],
            isEditable: $data['isEditable'],
        );
    }
}
