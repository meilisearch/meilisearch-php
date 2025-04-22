<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

class NetworkResults extends Data
{
    /**
     * @var non-empty-string the identifier for the local node
     */
    private string $self;

    /**
     * @var array<non-empty-string, array{url: non-empty-string, searchApiKey: string}> a mapping of remote node IDs to their connection details
     */
    private array $remotes;

    /**
     * @param array{
     *     self?: non-empty-string,
     *     remotes?: array<non-empty-string, array{url: non-empty-string, searchApiKey: string}>
     * } $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->self = $params['self'] ?? '';
        $this->remotes = $params['remotes'] ?? [];
    }

    /**
     * @return non-empty-string the identifier for the local node
     */
    public function getSelf(): string
    {
        return $this->self;
    }

    /**
     * @return array<non-empty-string, array{url: non-empty-string, searchApiKey: string}>
     */
    public function getRemotes(): array
    {
        return $this->remotes;
    }
}
