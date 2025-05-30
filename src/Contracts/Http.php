<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

use Meilisearch\Exceptions\ApiException;

interface Http
{
    /**
     * @throws ApiException
     * @throws JsonDecodingException
     */
    public function get(string $path, array $query = []);

    /**
     * @param non-empty-string|null $contentType
     *
     * @throws ApiException
     * @throws JsonEncodingException
     * @throws JsonDecodingException
     */
    public function post(string $path, $body = null, array $query = [], ?string $contentType = null);

    /**
     * @param non-empty-string|null $contentType
     *
     * @throws ApiException
     * @throws JsonEncodingException
     * @throws JsonDecodingException
     */
    public function put(string $path, $body = null, array $query = [], ?string $contentType = null);

    /**
     * @throws ApiException
     * @throws JsonEncodingException
     * @throws JsonDecodingException
     */
    public function patch(string $path, $body = null, array $query = []);

    /**
     * @throws ApiException
     * @throws JsonDecodingException
     */
    public function delete(string $path, array $query = []);
}
