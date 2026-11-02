<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskNetwork;

/**
 * @phpstan-type RawTaskImportData array{
 *     remoteName: non-empty-string,
 *     indexName?: non-empty-string|null,
 *     documentCount: non-negative-int
 * }
 */
final class TaskImportData
{
    /**
     * @param non-empty-string      $remoteName
     * @param non-empty-string|null $indexName
     * @param non-negative-int      $documentCount
     */
    public function __construct(
        public readonly string $remoteName,
        public readonly ?string $indexName,
        public readonly int $documentCount,
    ) {
    }

    /**
     * @param RawTaskImportData $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            remoteName: $data['remoteName'],
            indexName: $data['indexName'] ?? null,
            documentCount: $data['documentCount'],
        );
    }
}
