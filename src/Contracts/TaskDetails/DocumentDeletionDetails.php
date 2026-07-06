<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskDetails;

use Meilisearch\Contracts\TaskDetails;

/**
 * @phpstan-type RawDocumentDeletionDetails array{
 *     providedIds?: non-negative-int,
 *     originalFilter?: string|null,
 *     deletedDocuments?: non-negative-int|null
 * }
 *
 * @implements TaskDetails<RawDocumentDeletionDetails>
 */
final class DocumentDeletionDetails implements TaskDetails
{
    /**
     * @param non-negative-int|null $providedIds      number of documents queued for deletion
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
            $data['providedIds'] ?? null,
            $data['originalFilter'] ?? null,
            $data['deletedDocuments'] ?? null,
        );
    }

    public static function fromNullableArray(?array $data): ?self
    {
        if (null === $data || [] === $data) {
            return null;
        }

        /* @var RawDocumentDeletionDetails $data */
        return self::fromArray($data);
    }
}
