<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskNetwork;

use Meilisearch\Contracts\Task;
use Meilisearch\Contracts\TaskError;

/**
 * @phpstan-import-type RawTaskError from Task
 *
 * @phpstan-type RawRemoteTask array{
 *     taskUid?: non-negative-int,
 *     error: RawTaskError|null
 * }
 */
final class RemoteTask
{
    /**
     * @param non-negative-int|null $taskUid Uid of the task replicated on the remote. `null` if replication to the remote failed.
     */
    public function __construct(
        public readonly ?int $taskUid,
        public readonly ?TaskError $error,
    ) {
    }

    /**
     * A remote task carries either the `taskUid` it was enqueued with on the remote,
     * or the `error` that prevented it from being enqueued there.
     *
     * @param RawRemoteTask $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            taskUid: $data['taskUid'] ?? null,
            error: null !== $data['error'] ? TaskError::fromArray($data['error']) : null,
        );
    }
}
