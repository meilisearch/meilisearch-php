<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

use Psr\Http\Message\ResponseInterface;

final class InvalidResponseBodyException extends \Exception implements \Stringable, ExceptionInterface
{
    public readonly int $httpStatus;

    public function __construct(
        public readonly ResponseInterface $response,
        public readonly mixed $httpBody,
        ?\Throwable $previous = null,
    ) {
        $this->httpStatus = $response->getStatusCode();

        parent::__construct($this->getMessageFromHttpBody() ?? $response->getReasonPhrase(), $this->httpStatus, $previous);
    }

    public function __toString(): string
    {
        $base = 'Meilisearch InvalidResponseBodyException: Http Status: '.$this->httpStatus;

        if ('' !== $this->message) {
            $base .= ' - Message: '.$this->message;
        }

        return $base;
    }

    public function getMessageFromHttpBody(): ?string
    {
        if (null !== $this->httpBody) {
            $rawText = strip_tags($this->httpBody);

            if (!ctype_space($rawText)) {
                return substr(trim($rawText), 0, 100);
            }
        }

        return null;
    }
}
