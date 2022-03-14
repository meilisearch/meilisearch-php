<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use DateTime;
use MeiliSearch\Contracts\Endpoint;
use MeiliSearch\Contracts\Http;

class Keys extends Endpoint
{
    protected const PATH = '/keys';

    private ?string $key;
    private ?string $description;
    private ?array $actions;
    private ?array $indexes;
    private ?DateTime $expiresAt;
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;

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

    /**
     * @return $this
     */
    protected function newInstance(array $attributes): self
    {
        $this->key = $attributes['key'];
        $this->description = $attributes['description'];
        $this->actions = $attributes['actions'];
        $this->indexes = $attributes['indexes'];
        if ($attributes['expiresAt']) {
            $this->expiresAt = date_create_from_format('Y-m-d\TH:i:s\Z', $attributes['expiresAt']);
        }
        if ($attributes['createdAt']) {
            $this->createdAt = date_create_from_format('Y-m-d\TH:i:s.vu\Z', $attributes['createdAt']);
        }
        if ($attributes['updatedAt']) {
            $this->updatedAt = date_create_from_format('Y-m-d\TH:i:s.vu\Z', $attributes['updatedAt']);
        }

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

    public function getExpiresAt(): ?DateTime
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function get($key): self
    {
        $response = $this->http->get(self::PATH.'/'.$key);

        return $this->newInstance($response);
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
        if ($options['expiresAt'] && $options['expiresAt'] instanceof DateTime) {
            $options['expiresAt'] = $options['expiresAt']->format('Y-m-d\TH:i:s.vu\Z');
        }
        $response = $this->http->post(self::PATH, $options);

        return $this->newInstance($response);
    }

    public function update(string $key, array $options = []): self
    {
        if ($options['expiresAt'] && $options['expiresAt'] instanceof DateTime) {
            $options['expiresAt'] = $options['expiresAt']->format('Y-m-d\TH:i:s.vu\Z');
        }
        $response = $this->http->patch(self::PATH.'/'.$key, $options);

        return $this->newInstance($response);
    }

    public function delete(string $key): array
    {
        return $this->http->delete(self::PATH.'/'.$key) ?? [];
    }
}
