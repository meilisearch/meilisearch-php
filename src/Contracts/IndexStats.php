<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class IndexStats
{
    /**
     * @param non-negative-int                          $numberOfDocuments
     * @param int|string                                $rawDocumentDbSize         Bytes or human-readable string
     * @param int|string                                $avgDocumentSize           Bytes or human-readable string
     * @param non-negative-int                          $numberOfEmbeddings
     * @param non-negative-int                          $numberOfEmbeddedDocuments
     * @param array<non-empty-string, non-negative-int> $fieldDistribution
     * @param array<non-empty-string, int|string>|null  $internalDatabaseSizes     Present when showInternalDatabaseSizes=true; keys subject to change
     */
    public function __construct(
        private readonly int $numberOfDocuments,
        private readonly int|string $rawDocumentDbSize,
        private readonly int|string $avgDocumentSize,
        private readonly bool $isIndexing,
        private readonly int $numberOfEmbeddings,
        private readonly int $numberOfEmbeddedDocuments,
        private readonly array $fieldDistribution,
        private readonly ?array $internalDatabaseSizes = null,
    ) {
    }

    /**
     * @return non-negative-int
     */
    public function getNumberOfDocuments(): int
    {
        return $this->numberOfDocuments;
    }

    /**
     * Returns the raw document DB size.
     * Value is an integer (bytes) when `sizeFormat` is `'raw'` (default),
     * or a human-readable string such as `"2.3 MiB"` when `sizeFormat` is `'human'`.
     *
     * @return int|string
     */
    public function getRawDocumentDbSize(): int|string
    {
        return $this->rawDocumentDbSize;
    }

    /**
     * Returns the average document size.
     * Value is an integer (bytes) when `sizeFormat` is `'raw'` (default),
     * or a human-readable string such as `"2.3 MiB"` when `sizeFormat` is `'human'`.
     *
     * @return int|string
     */
    public function getAvgDocumentSize(): int|string
    {
        return $this->avgDocumentSize;
    }

    public function isIndexing(): bool
    {
        return $this->isIndexing;
    }

    /**
     * @return non-negative-int
     */
    public function getNumberOfEmbeddings(): int
    {
        return $this->numberOfEmbeddings;
    }

    /**
     * @return non-negative-int
     */
    public function getNumberOfEmbeddedDocuments(): int
    {
        return $this->numberOfEmbeddedDocuments;
    }

    /**
     * @return array<non-empty-string, non-negative-int>
     */
    public function getFieldDistribution(): array
    {
        return $this->fieldDistribution;
    }

    /**
     * Returns the internal database sizes map when requested via `showInternalDatabaseSizes=true`.
     * Returns null if the parameter was not set. Keys are subject to change.
     *
     * @return array<non-empty-string, int|string>|null
     */
    public function getInternalDatabaseSizes(): ?array
    {
        return $this->internalDatabaseSizes;
    }

    /**
     * @param array{
     *     numberOfDocuments: non-negative-int,
     *     rawDocumentDbSize: int|string,
     *     avgDocumentSize: int|string,
     *     isIndexing: bool,
     *     numberOfEmbeddings: non-negative-int,
     *     numberOfEmbeddedDocuments: non-negative-int,
     *     fieldDistribution: array<non-empty-string, non-negative-int>,
     *     internalDatabaseSizes?: array<non-empty-string, int|string>|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['numberOfDocuments'],
            $data['rawDocumentDbSize'],
            $data['avgDocumentSize'],
            $data['isIndexing'],
            $data['numberOfEmbeddings'],
            $data['numberOfEmbeddedDocuments'],
            $data['fieldDistribution'],
            $data['internalDatabaseSizes'] ?? null,
        );
    }
}
