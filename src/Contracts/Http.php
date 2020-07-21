<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

interface Http
{
    public function get($path, array $query = []);

    public function post(string $path, $body = null, array $query = []);

    public function put(string $path, $body = null, array $query = []);

    public function patch(string $path, $body = null, array $query = []);

    public function delete($path, array $query = []);
}
