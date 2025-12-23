<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type RemoteConfig array{url: non-empty-string, searchApiKey: non-empty-string, writeApiKey: non-empty-string}
 */
class NetworkResults extends Data
{
    /**
     * @var non-empty-string the identifier for the local node
     */
    private string $self;

    /**
     * @var non-empty-string the identifier for the leader node
     */
    private string $leader;

    /**
     * @var array<non-empty-string, RemoteConfig> a mapping of remote node IDs to their connection details
     */
    private array $remotes;

    /**
     * @param array{
     *     self?: non-empty-string,
     *     leader?: non-empty-string,
     *     remotes?: array<non-empty-string, RemoteConfig>
     * } $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->self = $params['self'] ?? '';
        $this->leader = $params['leader'] ?? '';
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
     * @return non-empty-string the identifier for the leader node
     */
    public function getLeader(): string
    {
        return $this->leader;
    }

    /**
     * @return array<non-empty-string, RemoteConfig>
     */
    public function getRemotes(): array
    {
        return $this->remotes;
    }
}
