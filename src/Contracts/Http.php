<?php

declare(strict_types=1);

namespace MeiliSearch\Contracts;

interface Http
{
    public function get(string $path, array $query = []);

    public function post(string $path, $body = null, array $query = [], string $contentType = null);

    public function put(string $path, $body = null, array $query = []);

    public function patch(string $path, $body = null, array $query = []);

    public function delete(string $path, array $query = []);
}
