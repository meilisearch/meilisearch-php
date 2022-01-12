<?php

declare(strict_types=1);

namespace MeiliSearch\Endpoints;

use MeiliSearch\Contracts\Endpoint;

class Keys extends Endpoint
{
    protected const PATH = '/keys';

    public function get($key): array
    {
        return $this->http->get(self::PATH.'/'.$key);
    }

    public function all(): array
    {
        return $this->http->get(self::PATH.'/');
    }

    public function create(array $options = []): array
    {
        return $this->http->post(self::PATH, $options);
    }

    public function update(string $key, array $options = []): array
    {
        return $this->http->patch(self::PATH.'/'.$key, $options);
    }

    public function delete(string $key): array
    {
        return $this->http->delete(self::PATH.'/'.$key) ?? [];
    }
}
