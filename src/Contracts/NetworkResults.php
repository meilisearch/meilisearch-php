<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class NetworkResults extends Data
{
    private string $self;
    private array $remotes;

    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->self = $params['self'] ?? '';
        $this->remotes = $params['remotes'] ?? [];
    }

    public function getSelf(): string
    {
        return $this->self;
    }

    public function getRemotes(): array
    {
        return $this->remotes;
    }
}
