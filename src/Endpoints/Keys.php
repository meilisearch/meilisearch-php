<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;

class Keys extends Endpoint
{
    protected const PATH = '/keys';

    private ?string $key;
    private ?string $description;
    private ?array $actions;
    private ?array $indexes;
    private ?string $expiresAt;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(Http $http, $key = null, $description = null, $actions = null, $indexes = null, $expiresAt = null, $createdAt = null, $updatedAt = null)
    {
        $this->key = $key;
        $this->description = $description;
        $this->actions = $actions;
        $this->indexes = $indexes;
        $this->expiresAt = $expiresAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;

        parent::__construct($http);
    }

    protected function newInstance(array $attributes): self
    {
        return new self(
            $this->http,
            $attributes['key'],
            $attributes['description'],
            $attributes['actions'],
            $attributes['indexes'],
            $attributes['expiresAt'],
            $attributes['createdAt'],
            $attributes['updatedAt'],
        );
    }

    /**
     * @return $this
     */
    protected function fill(array $attributes): self
    {
        $this->key = $attributes['key'];
        $this->description = $attributes['description'];
        $this->actions = $attributes['actions'];
        $this->indexes = $attributes['indexes'];
        $this->expiresAt = $attributes['expiresAt'];
        $this->createdAt = $attributes['createdAt'];
        $this->updatedAt = $attributes['updatedAt'];

        return $this;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getActions(): ?array
    {
        return $this->actions;
    }

    public function getIndexes(): ?array
    {
        return $this->indexes;
    }

    public function getExpiresAt(): ?string
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function get($key): self
    {
        $response = $this->http->get(self::PATH.'/'.$key);

        return $this->fill($response);
    }

    public function all(): array
    {
        $keys = [];

        foreach ($this->allRaw()['results'] as $key) {
            $keys[] = $this->newInstance($key);
        }

        return $keys;
    }

    public function allRaw(): array
    {
        return $this->http->get(self::PATH.'/');
    }

    public function create(array $options = []): self
    {
        $response = $this->http->post(self::PATH, $options);

        return $this->fill($response);
    }

    public function update(string $key, array $options = []): self
    {
        $response = $this->http->patch(self::PATH.'/'.$key, $options);

        return $this->fill($response);
    }

    public function delete(string $key): array
    {
        return $this->http->delete(self::PATH.'/'.$key) ?? [];
    }
}
