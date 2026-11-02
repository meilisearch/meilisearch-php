<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskNetwork;

/**
 * @phpstan-type RawTaskOrigin array{
 *     remoteName: non-empty-string,
 *     taskUid: non-negative-int,
 *     networkVersion?: non-empty-string
 * }
 */
final class TaskOrigin
{
    /**
     * @param non-empty-string      $remoteName
     * @param non-negative-int      $taskUid
     * @param non-empty-string|null $networkVersion
     */
    public function __construct(
        public readonly string $remoteName,
        public readonly int $taskUid,
        public readonly ?string $networkVersion,
    ) {
    }

    /**
     * @param RawTaskOrigin $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            remoteName: $data['remoteName'],
            taskUid: $data['taskUid'],
            networkVersion: $data['networkVersion'] ?? null,
        );
    }
}
