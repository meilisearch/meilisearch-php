<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

final class Task implements \ArrayAccess
{
    /**
     * @param non-negative-int      $taskUid
     * @param non-empty-string|null $indexUid
     * @param array<mixed>          $data     Raw data
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
        private readonly ?array $details = null,
        private readonly ?array $error = null,
        private readonly array $data = [],
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

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function getError(): ?array
    {
        return $this->error;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    // @todo: deprecate
    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->data);
    }

    // @todo: deprecate
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    // @todo: deprecate
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException(\sprintf('Setting data on "%s::%s" is not supported.', get_debug_type($this), $offset));
    }

    // @todo: deprecate
    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException(\sprintf('Unsetting data on "%s::%s" is not supported.', get_debug_type($this), $offset));
    }

    public function isFinished(): bool
    {
        return TaskStatus::Enqueued !== $this->status && TaskStatus::Processing !== $this->status;
    }

    /**
     * @param array{
     *     taskUid?: int,
     *     uid?: int,
     *     indexUid: non-empty-string,
     *     status: non-empty-string,
     *     type: non-empty-string,
     *     enqueuedAt: non-empty-string,
     *     startedAt?: non-empty-string,
     *     finishedAt?: non-empty-string,
     *     duration?: non-empty-string,
     *     canceledBy?: int,
     *     batchUid?: int,
     *     details?: array<mixed>,
     *     error?: array<mixed>,
     *     data: array<mixed>
     * } $data
     */
    public static function fromArray(array $data): Task
    {
        return new self(
            $data['taskUid'] ?? $data['uid'],
            $data['indexUid'],
            TaskStatus::from($data['status']),
            TaskType::from($data['type']),
            new \DateTimeImmutable($data['enqueuedAt']),
            isset($data['startedAt']) ? new \DateTimeImmutable($data['startedAt']) : null,
            isset($data['finishedAt']) ? new \DateTimeImmutable($data['finishedAt']) : null,
            $data['duration'] ?? null,
            $data['canceledBy'] ?? null,
            $data['batchUid'] ?? null,
            $data['details'] ?? null,
            $data['error'] ?? null,
            $data,
        );
    }
}
