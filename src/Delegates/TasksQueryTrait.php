<?php

declare(strict_types=1);

namespace MeiliSearch\Delegates;

trait TasksQueryTrait
{
    private int $next;
    private array $types;
    private array $statuses;
    private array $indexUids;
    private array $uids;
    private array $canceledBy;
    private \DateTime $beforeEnqueuedAt;
    private \DateTime $afterEnqueuedAt;
    private \DateTime $beforeStartedAt;
    private \DateTime $afterStartedAt;
    private \DateTime $beforeFinishedAt;
    private \DateTime $afterFinishedAt;

    public function setNext(int $next)
    {
        $this->next = $next;

        return $this;
    }

    public function setTypes(array $types)
    {
        $this->types = $types;

        return $this;
    }

    public function setStatuses(array $statuses)
    {
        $this->statuses = $statuses;

        return $this;
    }

    public function setIndexUids(array $indexUids)
    {
        $this->indexUids = $indexUids;

        return $this;
    }

    public function getIndexUids(): array
    {
        return $this->indexUids ?? [];
    }

    public function setUids(array $uids)
    {
        $this->uids = $uids;

        return $this;
    }

    public function setCanceledBy(array $canceledBy)
    {
        $this->canceledBy = $canceledBy;

        return $this;
    }

    public function setBeforeEnqueuedAt(\DateTime $date)
    {
        $this->beforeEnqueuedAt = $date;

        return $this;
    }

    public function setAfterEnqueuedAt(\DateTime $date)
    {
        $this->afterEnqueuedAt = $date;

        return $this;
    }

    public function setBeforeStartedAt(\DateTime $date)
    {
        $this->beforeStartedAt = $date;

        return $this;
    }

    public function setAfterStartedAt(\DateTime $date)
    {
        $this->afterStartedAt = $date;

        return $this;
    }

    public function setBeforeFinishedAt(\DateTime $date)
    {
        $this->beforeFinishedAt = $date;

        return $this;
    }

    public function setAfterFinishedAt(\DateTime $date)
    {
        $this->afterFinishedAt = $date;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'next' => $this->next ?? null,
            'beforeEnqueuedAt' => $this->formatDate($this->beforeEnqueuedAt ?? null),
            'afterEnqueuedAt' => $this->formatDate($this->afterEnqueuedAt ?? null),
            'beforeStartedAt' => $this->formatDate($this->beforeStartedAt ?? null),
            'afterStartedAt' => $this->formatDate($this->afterStartedAt ?? null),
            'beforeFinishedAt' => $this->formatDate($this->beforeFinishedAt ?? null),
            'afterFinishedAt' => $this->formatDate($this->afterFinishedAt ?? null),
            'statuses' => $this->formatArray($this->statuses ?? null),
            'uids' => $this->formatArray($this->uids ?? null),
            'canceledBy' => $this->formatArray($this->canceledBy ?? null),
            'types' => $this->formatArray($this->types ?? null),
            'indexUids' => $this->formatArray($this->indexUids ?? null),
        ], function ($item) { return null != $item || is_numeric($item); });
    }

    private function formatDate(?\DateTime $date)
    {
        return isset($date) ? $date->format(\DateTime::RFC3339) : null;
    }

    private function formatArray(?array $arr)
    {
        return isset($arr) ? implode(',', $arr) : null;
    }
}
