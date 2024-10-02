<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints\Delegates;

trait TasksQueryTrait
{
    private array $types;
    private array $statuses;
    private array $indexUids;
    private array $uids;
    private \DateTimeInterface $beforeEnqueuedAt;
    private \DateTimeInterface $afterEnqueuedAt;
    private \DateTimeInterface $beforeStartedAt;
    private \DateTimeInterface $afterStartedAt;
    private \DateTimeInterface $beforeFinishedAt;
    private \DateTimeInterface $afterFinishedAt;

    /**
     * @return $this
     */
    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @return $this
     */
    public function setStatuses(array $statuses): self
    {
        $this->statuses = $statuses;

        return $this;
    }

    /**
     * @return $this
     */
    public function setIndexUids(array $indexUids): self
    {
        $this->indexUids = $indexUids;

        return $this;
    }

    public function getIndexUids(): array
    {
        return $this->indexUids ?? [];
    }

    /**
     * @return $this
     */
    public function setUids(array $uids): self
    {
        $this->uids = $uids;

        return $this;
    }

    /**
     * @return $this
     */
    public function setBeforeEnqueuedAt(\DateTimeInterface $date): self
    {
        $this->beforeEnqueuedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAfterEnqueuedAt(\DateTimeInterface $date): self
    {
        $this->afterEnqueuedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setBeforeStartedAt(\DateTimeInterface $date): self
    {
        $this->beforeStartedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAfterStartedAt(\DateTimeInterface $date): self
    {
        $this->afterStartedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setBeforeFinishedAt(\DateTimeInterface $date): self
    {
        $this->beforeFinishedAt = $date;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAfterFinishedAt(\DateTimeInterface $date): self
    {
        $this->afterFinishedAt = $date;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter(
            $this->baseArray(),
            static function ($item) { return null !== $item; }
        );
    }

    protected function baseArray(): array
    {
        return [
            'beforeEnqueuedAt' => $this->formatDate($this->beforeEnqueuedAt ?? null),
            'afterEnqueuedAt' => $this->formatDate($this->afterEnqueuedAt ?? null),
            'beforeStartedAt' => $this->formatDate($this->beforeStartedAt ?? null),
            'afterStartedAt' => $this->formatDate($this->afterStartedAt ?? null),
            'beforeFinishedAt' => $this->formatDate($this->beforeFinishedAt ?? null),
            'afterFinishedAt' => $this->formatDate($this->afterFinishedAt ?? null),
            'statuses' => $this->formatArray($this->statuses ?? null),
            'uids' => $this->formatArray($this->uids ?? null),
            'types' => $this->formatArray($this->types ?? null),
            'indexUids' => $this->formatArray($this->indexUids ?? null),
        ];
    }

    private function formatDate(?\DateTimeInterface $date): ?string
    {
        return null !== $date ? $date->format(\DateTimeInterface::RFC3339) : null;
    }

    private function formatArray(?array $array): ?string
    {
        return null !== $array ? implode(',', $array) : null;
    }
}
