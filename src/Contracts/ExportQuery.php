<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * Request body of `POST /export`, which sends documents and settings to another Meilisearch instance.
 *
 * @phpstan-import-type RawExportIndexOptions from ExportIndexOptions
 *
 * @phpstan-type RawExportQuery array{
 *     url: non-empty-string,
 *     apiKey?: non-empty-string,
 *     payloadSize?: non-empty-string|positive-int,
 *     indexes?: array<non-empty-string, RawExportIndexOptions>
 * }
 */
final class ExportQuery implements \JsonSerializable
{
    /**
     * @var non-empty-string|null
     */
    private ?string $apiKey = null;

    /**
     * @var non-empty-string|positive-int|null
     */
    private string|int|null $payloadSize = null;

    /**
     * @var array<non-empty-string, ExportIndexOptions>|null
     */
    private ?array $indexes = null;

    /**
     * @param non-empty-string $url URL of the destination instance
     */
    public function __construct(
        private readonly string $url,
    ) {
    }

    /**
     * @param non-empty-string|null $apiKey API key of the destination instance, with permission to add documents and update settings
     *
     * @return $this
     */
    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @param non-empty-string|positive-int|null $payloadSize maximum size of each payload sent to the destination, in bytes or as a human-readable string such as "50 MiB"
     *
     * @return $this
     */
    public function setPayloadSize(string|int|null $payloadSize): self
    {
        $this->payloadSize = $payloadSize;

        return $this;
    }

    /**
     * @param array<non-empty-string, ExportIndexOptions>|null $indexes options keyed by index uid pattern, e.g. `movies*` or `*`; null exports all indexes
     *
     * @return $this
     */
    public function setIndexes(?array $indexes): self
    {
        $this->indexes = $indexes;

        return $this;
    }

    /**
     * @return RawExportQuery
     */
    public function toArray(): array
    {
        return array_filter([
            'url' => $this->url,
            'apiKey' => $this->apiKey,
            'payloadSize' => $this->payloadSize,
            'indexes' => null !== $this->indexes
                ? array_map(static fn (ExportIndexOptions $options) => $options->toArray(), $this->indexes)
                : null,
        ], static function ($item) { return null !== $item; });
    }

    public function jsonSerialize(): object
    {
        return (object) array_filter([
            'url' => $this->url,
            'apiKey' => $this->apiKey,
            'payloadSize' => $this->payloadSize,
            'indexes' => null !== $this->indexes ? (object) $this->indexes : null,
        ], static function ($item) { return null !== $item; });
    }
}
