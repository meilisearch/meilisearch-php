<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @implements TaskDetails<array{
 *     providedIds: non-negative-int,
 *     originalFilter: string|null,
 *     deletedDocuments: non-negative-int|null
 * }>
 */
final class DocumentDeletionDetails implements TaskDetails
{
    /**
     * @param non-negative-int|null $providedIds      Number of documents queued for deletion
     * @param string|null           $originalFilter   The filter used to delete documents. Null if it was not specified.
     * @param int|null              $deletedDocuments Number of documents deleted. `null` while the task status is enqueued or processing.
     */
    public function __construct(
        public readonly ?int $providedIds,
        public readonly ?string $originalFilter,
        public readonly ?int $deletedDocuments,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['providedIds'],
            $data['originalFilter'] ?? null,
            $data['deletedDocuments'] ?? null,
        );
    }
}
