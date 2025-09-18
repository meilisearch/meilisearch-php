<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

use Psr\Http\Message\ResponseInterface;

class InvalidResponseBodyException extends \Exception implements ExceptionInterface
{
    public $httpStatus = 0;
    public $httpBody;
    public $message;

    public function __construct(ResponseInterface $response, $httpBody, $previous = null)
    {
        $this->httpStatus = $response->getStatusCode();
        $this->httpBody = $httpBody;
        $this->message = $this->getMessageFromHttpBody() ?? $response->getReasonPhrase();

        parent::__construct($this->message, $this->httpStatus, $previous);
    }

    public function __toString()
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
