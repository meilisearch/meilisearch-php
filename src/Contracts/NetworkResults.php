<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

/**
 * @phpstan-type RemoteConfig array{url: non-empty-string, searchApiKey: non-empty-string, writeApiKey: non-empty-string}
 */
class NetworkResults extends Data
{
    /**
     * @var non-empty-string|null the identifier for the local node
     */
    private ?string $self;

    /**
     * @var non-empty-string|null the identifier for the leader node
     */
    private ?string $leader;

    /**
     * @var non-empty-string|null the version of the network configuration
     */
    private ?string $version;

    /**
     * @var array<non-empty-string, RemoteConfig|null> a mapping of remote node IDs to their connection details
     */
    private array $remotes;

    /**
     * @param array{
     *     self?: non-empty-string|null,
     *     leader?: non-empty-string|null,
     *     version?: non-empty-string|null,
     *     remotes?: array<non-empty-string, RemoteConfig|null>
     * } $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->self = $params['self'] ?? null;
        $this->leader = $params['leader'] ?? null;
        $this->version = $params['version'] ?? null;
        $this->remotes = $params['remotes'] ?? [];
    }

    /**
     * @return non-empty-string|null the identifier for the local node
     */
    public function getSelf(): ?string
    {
        return $this->self;
    }

    /**
     * @return non-empty-string|null the identifier for the leader node
     */
    public function getLeader(): ?string
    {
        return $this->leader;
    }

    /**
     * @return non-empty-string|null the version of the network configuration
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @return array<non-empty-string, RemoteConfig|null>
     */
    public function getRemotes(): array
    {
        return $this->remotes;
    }
}
