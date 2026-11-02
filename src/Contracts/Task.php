<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Contracts\TaskDetails\DocumentAdditionOrUpdateDetails;
use Meilisearch\Contracts\TaskDetails\DocumentDeletionDetails;
use Meilisearch\Contracts\TaskDetails\DocumentEditionDetails;
use Meilisearch\Contracts\TaskDetails\DumpCreationDetails;
use Meilisearch\Contracts\TaskDetails\IndexCompactionDetails;
use Meilisearch\Contracts\TaskDetails\IndexCreationDetails;
use Meilisearch\Contracts\TaskDetails\IndexDeletionDetails;
use Meilisearch\Contracts\TaskDetails\IndexSwapDetails;
use Meilisearch\Contracts\TaskDetails\IndexUpdateDetails;
use Meilisearch\Contracts\TaskDetails\SettingsUpdateDetails;
use Meilisearch\Contracts\TaskDetails\TaskCancelationDetails;
use Meilisearch\Contracts\TaskDetails\TaskDeletionDetails;
use Meilisearch\Contracts\TaskDetails\UnknownTaskDetails;
use Meilisearch\Contracts\TaskNetwork\ImportNetwork;
use Meilisearch\Contracts\TaskNetwork\OriginNetwork;
use Meilisearch\Contracts\TaskNetwork\RemoteTasksNetwork;
use Meilisearch\Contracts\TaskNetwork\UnknownTaskNetwork;
use Meilisearch\Exceptions\LogicException;

/**
 * @phpstan-type RawTaskError array{
 *     message: non-empty-string,
 *     code: non-empty-string,
 *     type: non-empty-string,
 *     link: non-empty-string
 * }
 * @phpstan-type RawTask array{
 *     taskUid?: non-negative-int,
 *     uid?: non-negative-int,
 *     indexUid?: non-empty-string,
 *     status: non-empty-string,
 *     type: non-empty-string,
 *     enqueuedAt: non-empty-string,
 *     startedAt?: non-empty-string|null,
 *     finishedAt?: non-empty-string|null,
 *     duration?: non-empty-string|null,
 *     canceledBy?: non-negative-int,
 *     batchUid?: non-negative-int,
 *     details?: array<mixed>|null,
 *     error?: RawTaskError|null,
 *     network?: array<mixed>|null
 * }
 *
 * Raw detail shapes are imported here so Task can choose the concrete detail
 * class while keeping shape validation at this boundary.
 *
 * @phpstan-import-type RawDocumentAdditionOrUpdateDetails from DocumentAdditionOrUpdateDetails
 * @phpstan-import-type RawDocumentDeletionDetails from DocumentDeletionDetails
 * @phpstan-import-type RawDocumentEditionDetails from DocumentEditionDetails
 * @phpstan-import-type RawDumpCreationDetails from DumpCreationDetails
 * @phpstan-import-type RawIndexCompactionDetails from IndexCompactionDetails
 * @phpstan-import-type RawIndexCreationDetails from IndexCreationDetails
 * @phpstan-import-type RawIndexDeletionDetails from IndexDeletionDetails
 * @phpstan-import-type RawIndexSwapDetails from IndexSwapDetails
 * @phpstan-import-type RawIndexUpdateDetails from IndexUpdateDetails
 * @phpstan-import-type RawSettingsUpdateDetails from SettingsUpdateDetails
 * @phpstan-import-type RawTaskCancelationDetails from TaskCancelationDetails
 * @phpstan-import-type RawTaskDeletionDetails from TaskDeletionDetails
 * @phpstan-import-type RawRemoteTasksNetwork from RemoteTasksNetwork
 * @phpstan-import-type RawOriginNetwork from OriginNetwork
 * @phpstan-import-type RawImportNetwork from ImportNetwork
 */
final class Task
{
    /**
     * @param non-negative-int                   $taskUid
     * @param non-empty-string|null              $indexUid
     * @param non-empty-string|null              $duration
     * @param \Closure(int, int, int): Task|null $await
     */
    public function __construct(
        private readonly int $taskUid,
        private readonly ?string $indexUid,
        private readonly TaskStatus $status,
        private readonly TaskType $type,
        private readonly \DateTimeImmutable $enqueuedAt,
        private readonly ?\DateTimeImmutable $startedAt = null,
        private readonly ?\DateTimeImmutable $finishedAt = null,
        private readonly ?string $duration = null,
        private readonly ?int $canceledBy = null,
        private readonly ?int $batchUid = null,
        private readonly ?TaskDetails $details = null,
        private readonly ?TaskError $error = null,
        private readonly array $raw = [],
        private readonly ?\Closure $await = null,
        private readonly ?TaskNetwork $network = null,
    ) {
    }

    /**
     * @return non-negative-int
     */
    public function getTaskUid(): int
    {
        return $this->taskUid;
    }

    /**
     * @return non-empty-string|null
     */
    public function getIndexUid(): ?string
    {
        return $this->indexUid;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getType(): TaskType
    {
        return $this->type;
    }

    public function getEnqueuedAt(): \DateTimeImmutable
    {
        return $this->enqueuedAt;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    /**
     * @return non-empty-string|null
     */
    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function getCanceledBy(): ?int
    {
        return $this->canceledBy;
    }

    public function getBatchUid(): ?int
    {
        return $this->batchUid;
    }

    public function getDetails(): ?TaskDetails
    {
        return $this->details;
    }

    public function getError(): ?TaskError
    {
        return $this->error;
    }

    public function getNetwork(): ?TaskNetwork
    {
        return $this->network;
    }

    public function isFinished(): bool
    {
        return TaskStatus::Enqueued !== $this->status && TaskStatus::Processing !== $this->status;
    }

    public function wait(int $timeoutInMs = 5000, int $intervalInMs = 50): Task
    {
        if ($this->isFinished()) {
            return $this;
        }

        if (null !== $this->await) {
            return ($this->await)($this->taskUid, $timeoutInMs, $intervalInMs);
        }

        throw new LogicException(\sprintf('Cannot wait for task because wait function is not provided.'));
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->raw;
    }

    /**
     * @param RawTask                            $data
     * @param \Closure(int, int, int): Task|null $await
     */
    public static function fromArray(array $data, ?\Closure $await = null): Task
    {
        $details = $data['details'] ?? null;
        $type = TaskType::tryFrom($data['type']) ?? TaskType::Unknown;
        $error = null;

        if (\array_key_exists('error', $data) && null !== $data['error']) {
            /** @phpstan-var RawTaskError $errorData */
            $errorData = $data['error'];
            $error = TaskError::fromArray($errorData);
        }

        return new self(
            $data['taskUid'] ?? $data['uid'],
            $data['indexUid'] ?? null,
            TaskStatus::tryFrom($data['status']) ?? TaskStatus::Unknown,
            $type,
            new \DateTimeImmutable($data['enqueuedAt']),
            \array_key_exists('startedAt', $data) && null !== $data['startedAt'] ? new \DateTimeImmutable($data['startedAt']) : null,
            \array_key_exists('finishedAt', $data) && null !== $data['finishedAt'] ? new \DateTimeImmutable($data['finishedAt']) : null,
            $data['duration'] ?? null,
            $data['canceledBy'] ?? null,
            $data['batchUid'] ?? null,
            self::detailsFromArray($type, $details),
            $error,
            $data,
            $await,
            network: self::networkFromArray($data['network'] ?? null),
        );
    }

    /**
     * @param array<mixed>|null $network
     */
    private static function networkFromArray(?array $network): ?TaskNetwork
    {
        if (null === $network) {
            return null;
        }

        // `remote_tasks` is often an empty JSON object, which decodes to [], so test the type rather than truthiness.
        // Shapes that do not match a known variant are preserved as UnknownTaskNetwork.
        if (\is_array($network['remote_tasks'] ?? null)) {
            /** @phpstan-var RawRemoteTasksNetwork $remoteTasksNetwork */
            $remoteTasksNetwork = $network;

            return RemoteTasksNetwork::fromArray($remoteTasksNetwork);
        }

        if (\is_array($network['origin'] ?? null)) {
            /** @phpstan-var RawOriginNetwork $originNetwork */
            $originNetwork = $network;

            return OriginNetwork::fromArray($originNetwork);
        }

        if (\is_array($network['import_from'] ?? null) && \is_array($network['network_change'] ?? null)) {
            /** @phpstan-var RawImportNetwork $importNetwork */
            $importNetwork = $network;

            return ImportNetwork::fromArray($importNetwork);
        }

        return UnknownTaskNetwork::fromArray($network);
    }

    /**
     * @param array<mixed>|null $details
     */
    private static function detailsFromArray(TaskType $type, ?array $details): ?TaskDetails
    {
        // Unknown task types are forward-compatible: preserve whatever payload we got.
        if (TaskType::Unknown === $type) {
            return UnknownTaskDetails::fromArray($details ?? []);
        }

        // For known task types, null/empty details should map to "no details object".
        if (null === $details || [] === $details) {
            return null;
        }

        // match keeps the dispatch exhaustive and lets PHPStan flag missing enum cases.
        return match ($type) {
            TaskType::IndexCreation => self::detailFromArray($details, IndexCreationDetails::fromArray(...)),
            TaskType::IndexUpdate => self::detailFromArray($details, IndexUpdateDetails::fromArray(...)),
            TaskType::IndexDeletion => self::detailFromArray($details, IndexDeletionDetails::fromArray(...)),
            TaskType::IndexSwap => self::detailFromArray($details, IndexSwapDetails::fromArray(...)),
            TaskType::DocumentAdditionOrUpdate => self::detailFromArray($details, DocumentAdditionOrUpdateDetails::fromArray(...)),
            TaskType::DocumentDeletion => self::detailFromArray($details, DocumentDeletionDetails::fromArray(...)),
            TaskType::DocumentEdition => self::detailFromArray($details, DocumentEditionDetails::fromArray(...)),
            TaskType::SettingsUpdate => self::detailFromArray($details, SettingsUpdateDetails::fromArray(...)),
            TaskType::DumpCreation => self::detailFromArray($details, DumpCreationDetails::fromArray(...)),
            TaskType::TaskCancelation => self::detailFromArray($details, TaskCancelationDetails::fromArray(...)),
            TaskType::TaskDeletion => self::detailFromArray($details, TaskDeletionDetails::fromArray(...)),
            // It’s intentional that SnapshotCreation tasks don’t have a details object
            // (no SnapshotCreationDetails exists and tests don’t exercise any details)
            TaskType::SnapshotCreation, TaskType::NetworkTopologyChange, TaskType::DsrUpdate, TaskType::DsrClear => null,
            TaskType::IndexCompaction => self::detailFromArray($details, IndexCompactionDetails::fromArray(...)),
        };
    }

    /**
     * @template TShape of array
     * @template TDetails of TaskDetails
     *
     * @param array<mixed>               $details
     * @param callable(TShape): TDetails $factory
     *
     * @return TDetails
     */
    private static function detailFromArray(array $details, callable $factory): TaskDetails
    {
        // PHPStan cannot infer the precise shape from TaskType in this switch.
        // We cast once here, then delegate to the concrete fromArray() factory.
        /** @var TShape $typedDetails */
        $typedDetails = $details;

        return $factory($typedDetails);
    }
}
