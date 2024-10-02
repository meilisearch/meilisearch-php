<?php

declare(strict_types=1);

namespace Meilisearch\Endpoints;

use Meilisearch\Contracts\Endpoint;
use Meilisearch\Contracts\Http;
use Meilisearch\Contracts\KeysQuery;
use Meilisearch\Contracts\KeysResults;

class Keys extends Endpoint
{
    protected const PATH = '/keys';

    protected Keys $keys;
    protected Http $http;

    private ?string $uid;
    private ?string $name;
    private ?string $key;
    private ?string $description;
    private ?array $actions;
    private ?array $indexes;
    private ?\DateTimeInterface $expiresAt;
    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;

    public function __construct(Http $http, ?string $uid = null, ?string $name = null, ?string $key = null, ?string $description = null, ?array $actions = null, ?array $indexes = null, ?\DateTimeInterface $expiresAt = null, ?\DateTimeInterface $createdAt = null, ?\DateTimeInterface $updatedAt = null)
    {
        $this->uid = $uid;
        $this->name = $name;
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
        $key = new self(
            $this->http,
            $attributes['uid'],
            $attributes['name'],
            $attributes['key'],
            $attributes['description'],
            $attributes['actions'],
            $attributes['indexes'],
        );
        if ($attributes['expiresAt']) {
            $key->expiresAt = $this->createDate($attributes['expiresAt']);
        }
        if ($attributes['createdAt']) {
            $key->createdAt = $this->createDate($attributes['createdAt']);
        }
        if ($attributes['updatedAt']) {
            $key->updatedAt = $this->createDate($attributes['updatedAt']);
        }

        return $key;
    }

    /**
     * @return $this
     */
    protected function fill(array $attributes): self
    {
        $this->uid = $attributes['uid'];
        $this->name = $attributes['name'];
        $this->key = $attributes['key'];
        $this->description = $attributes['description'];
        $this->actions = $attributes['actions'];
        $this->indexes = $attributes['indexes'];
        if ($attributes['expiresAt']) {
            $this->expiresAt = $this->createDate($attributes['expiresAt']);
        }
        if ($attributes['createdAt']) {
            $this->createdAt = $this->createDate($attributes['createdAt']);
        }
        if ($attributes['updatedAt']) {
            $this->updatedAt = $this->createDate($attributes['updatedAt']);
        }

        return $this;
    }

    protected function createDate($attribute): ?\DateTimeInterface
    {
        if (!\is_string($attribute)) {
            return null;
        }

        if (false === strpos($attribute, '.')) {
            $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $attribute);
        } else {
            $attribute = preg_replace('/(\.\d{6})\d+/', '$1', $attribute, 1);
            $date = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s.uP', $attribute);
        }

        return false === $date ? null : $date;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function get($keyOrUid): self
    {
        $response = $this->http->get(self::PATH.'/'.$keyOrUid);

        return $this->fill($response);
    }

    public function all(?KeysQuery $options = null): KeysResults
    {
        $query = isset($options) ? $options->toArray() : [];

        $keys = [];
        $response = $this->allRaw($query);

        foreach ($response['results'] as $key) {
            $keys[] = $this->newInstance($key);
        }

        $response['results'] = $keys;

        return new KeysResults($response);
    }

    public function allRaw(array $options = []): array
    {
        return $this->http->get(self::PATH.'/', $options);
    }

    public function create(array $options = []): self
    {
        if (isset($options['expiresAt']) && $options['expiresAt'] instanceof \DateTimeInterface) {
            $options['expiresAt'] = $options['expiresAt']->format('Y-m-d\TH:i:s.vu\Z');
        }
        $response = $this->http->post(self::PATH, $options);

        return $this->fill($response);
    }

    public function update(string $keyOrUid, array $options = []): self
    {
        $data = array_intersect_key($options, array_flip(['description', 'name']));
        $response = $this->http->patch(self::PATH.'/'.$keyOrUid, $data);

        return $this->fill($response);
    }

    public function delete(string $keyOrUid): array
    {
        return $this->http->delete(self::PATH.'/'.$keyOrUid) ?? [];
    }
}
