<?php

declare(strict_types=1);

namespace Meilisearch\Contracts;

final class TaskError
{
    /**
     * @param non-empty-string $message
     * @param non-empty-string $code
     * @param non-empty-string $type
     * @param non-empty-string $link
     */
    public function __construct(
        public readonly string $message,
        public readonly string $code,
        public readonly string $type,
        public readonly string $link,
    ) {
    }

    /**
     * @param array{
     *     message: non-empty-string,
     *     code: non-empty-string,
     *     type: non-empty-string,
     *     link: non-empty-string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['message'],
            $data['code'],
            $data['type'],
            $data['link'],
        );
    }
}
