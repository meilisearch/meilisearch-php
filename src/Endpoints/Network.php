<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\NetworkResults;
use Meilisearch\Contracts\Task;

use function Meilisearch\partial;

/**
 * @phpstan-import-type RemoteConfig from NetworkResults
 *
 * @phpstan-type ShardConfig array{
 *   remotes?: list<non-empty-string>,
 *   addRemotes?: list<non-empty-string>,
 *   removeRemotes?: list<non-empty-string>
 * }
 */
class Network extends Endpoint
{
    protected const PATH = '/network';

    /**
     * @return array{
     *     self?: non-empty-string|null,
     *     leader?: non-empty-string|null,
     *     version?: non-empty-string|null,
     *     remotes?: array<non-empty-string, RemoteConfig|null>,
     *     shards?: array<non-empty-string, array{remotes: list<non-empty-string>}>
     * }
     */
    public function get(): array
    {
        return $this->http->get(self::PATH);
    }

    /**
     * @param array{
     *     self: non-empty-string,
     *     leader: non-empty-string,
     *     remotes: array<non-empty-string, RemoteConfig>,
     *     shards: array<non-empty-string, ShardConfig>,
     * } $options
     */
    public function initialize(array $options): Task
    {
        $this->validateInitializeOptions($options);

        $body = [
            'self' => $options['self'],
            'leader' => $options['leader'],
            'remotes' => $options['remotes'],
            'shards' => $options['shards'],
        ];

        return $this->dispatchPatch($body);
    }

    /**
     * Add a remote to the network.
     *
     * @param non-empty-string $name
     * @param RemoteConfig     $remote
     */
    public function addRemote(string $name, array $remote): Task
    {
        $this->assertNonEmptyString($name, 'remote name');
        $this->validateRemotes([$name => $remote]);

        $body = [
            'remotes' => [
                $name => $remote,
            ],
        ];

        return $this->dispatchPatch($body);
    }

    /**
     * Remove a remote from the network.
     *
     * @param non-empty-string $name
     */
    public function removeRemote(string $name): Task
    {
        $this->assertNonEmptyString($name, 'remote name');

        $body = [
            'remotes' => [
                $name => null,
            ],
        ];

        return $this->dispatchPatch($body);
    }

    /**
     * @param non-empty-string       $shardName
     * @param list<non-empty-string> $remoteNames
     */
    public function addRemotesToShard(string $shardName, array $remoteNames): Task
    {
        $this->assertNonEmptyString($shardName, 'shardName');
        $remoteList = $this->assertRemoteNames($remoteNames);

        $body = [
            'shards' => [
                $shardName => ['addRemotes' => $remoteList],
            ],
        ];

        return $this->dispatchPatch($body);
    }

    /**
     * @param non-empty-string       $shardName
     * @param list<non-empty-string> $remoteNames
     */
    public function removeRemotesFromShard(string $shardName, array $remoteNames): Task
    {
        $this->assertNonEmptyString($shardName, 'shardName');
        $remoteList = $this->assertRemoteNames($remoteNames);

        $body = [
            'shards' => [
                $shardName => ['removeRemotes' => $remoteList],
            ],
        ];

        return $this->dispatchPatch($body);
    }

    /**
     * @param array{
     *     self: mixed,
     *     leader: mixed,
     *     remotes: mixed,
     *     shards: mixed,
     * } $options
     */
    private function validateInitializeOptions(array $options): void
    {
        $this->assertNonEmptyString($options['self'] ?? null, 'self');
        $this->assertNonEmptyString($options['leader'] ?? null, 'leader');
        $remotes = $options['remotes'] ?? null;
        $shards = $options['shards'] ?? null;

        if (!\is_array($remotes) || [] === $remotes) {
            throw new \InvalidArgumentException('remotes must be a non-empty array of remote definitions.');
        }

        $validatedRemotes = $this->validateRemotes($remotes);

        if (!\array_key_exists($options['self'], $validatedRemotes)) {
            throw new \InvalidArgumentException('self must be one of the defined remotes.');
        }

        if (!\array_key_exists($options['leader'], $validatedRemotes)) {
            throw new \InvalidArgumentException('leader must be one of the defined remotes.');
        }

        if (!\is_array($shards) || [] === $shards) {
            throw new \InvalidArgumentException('shards must be a non-empty array.');
        }

        foreach ($shards as $shardName => $definition) {
            $this->assertNonEmptyString($shardName, 'shard name');

            if (!\is_array($definition)) {
                throw new \InvalidArgumentException('shard definition must be an array with remotes.');
            }

            $remotesList = $definition['remotes'] ?? null;

            if (!\is_array($remotesList) || [] === $remotesList) {
                throw new \InvalidArgumentException('each shard must define at least one remote.');
            }

            foreach ($remotesList as $remoteName) {
                $this->assertNonEmptyString($remoteName, 'shard remote');

                if (!\array_key_exists($remoteName, $validatedRemotes)) {
                    throw new \InvalidArgumentException(\sprintf('shard references unknown remote "%s".', $remoteName));
                }
            }
        }
    }

    /**
     * @param array<non-empty-string, RemoteConfig> $remotes
     *
     * @return array<non-empty-string, RemoteConfig>
     */
    private function validateRemotes(array $remotes): array
    {
        $validated = [];

        foreach ($remotes as $name => $config) {
            $this->assertNonEmptyString($name, 'remote name');

            if (!\is_array($config)) {
                throw new \InvalidArgumentException('remote configuration must be an array.');
            }

            foreach (['url', 'searchApiKey', 'writeApiKey'] as $field) {
                if (!\is_string($config[$field] ?? null) || '' === $config[$field]) {
                    throw new \InvalidArgumentException(\sprintf('remote "%s" is missing a valid %s.', $name, $field));
                }
            }

            /* @var RemoteConfig $config */
            $validated[$name] = $config;
        }

        return $validated;
    }

    /**
     * @param list<mixed> $remoteNames
     *
     * @return list<non-empty-string>
     */
    private function assertRemoteNames(array $remoteNames): array
    {
        if ([] === $remoteNames) {
            throw new \InvalidArgumentException('remoteNames must be a non-empty list of strings.');
        }

        $list = [];

        foreach ($remoteNames as $name) {
            $this->assertNonEmptyString($name, 'remote name');
            $list[] = $name;
        }

        return $list;
    }

    private function assertNonEmptyString(mixed $value, string $field): void
    {
        if (!\is_string($value) || '' === $value) {
            throw new \InvalidArgumentException(\sprintf('%s must be a non-empty string.', $field));
        }
    }

    /**
     * @param array<mixed> $body
     */
    private function dispatchPatch(array $body): Task
    {
        return Task::fromArray($this->http->patch(self::PATH, $body), partial(Tasks::waitTask(...), $this->http));
    }
}
