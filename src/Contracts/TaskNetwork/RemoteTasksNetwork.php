<?php

declare(strict_types=1);

namespace Meilisearch\Contracts\TaskNetwork;

use Meilisearch\Contracts\TaskNetwork;

/**
 * @phpstan-import-type RawRemoteTask from RemoteTask
 *
 * @phpstan-type RawRemoteTasksNetwork array{
 *     remote_tasks: array<non-empty-string, RawRemoteTask>,
 *     network_version?: non-empty-string
 * }
 *
 * @implements TaskNetwork<RawRemoteTasksNetwork>
 */
final class RemoteTasksNetwork implements TaskNetwork
{
    /**
     * @param array<non-empty-string, RemoteTask> $remoteTasks
     * @param non-empty-string|null               $networkVersion
     */
    public function __construct(
        public readonly array $remoteTasks,
        public readonly ?string $networkVersion,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            remoteTasks: array_map(RemoteTask::fromArray(...), $data['remote_tasks']),
            networkVersion: $data['network_version'] ?? null,
        );
    }
}
