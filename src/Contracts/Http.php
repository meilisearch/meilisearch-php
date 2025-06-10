<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Exceptions\ApiException;

interface Http
{
    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function get(string $path, array $query = []);

    /**
     * @param non-empty-string|null $contentType
     *
     * @throws ApiException
     * @throws \JsonException
     */
    public function post(string $path, mixed $body = null, array $query = [], ?string $contentType = null);

    /**
     * @param non-empty-string|null $contentType
     *
     * @throws ApiException
     * @throws \JsonException
     */
    public function put(string $path, mixed $body = null, array $query = [], ?string $contentType = null);

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function patch(string $path, mixed $body = null, array $query = []);

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function delete(string $path, array $query = []);
}
