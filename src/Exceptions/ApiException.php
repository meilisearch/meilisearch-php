<?php

declare(strict_types=1);

namespace Meilisearch\Exceptions;

use Psr\Http\Message\ResponseInterface;

class ApiException extends \Exception implements ExceptionInterface
{
    public $httpStatus = 0;
    public $message;
    public ?string $errorCode;
    public ?string $errorType;
    public ?string $errorLink;
    public $httpBody;

    public const HINT_MESSAGE = "Hint: It might not be working because maybe you're not up to date with the Meilisearch version that `%s` call requires.";

    public function __construct(ResponseInterface $response, $httpBody, $previous = null)
    {
        $this->httpBody = $httpBody;
        $this->httpStatus = $response->getStatusCode();
        $this->message = $this->getMessageFromHttpBody() ?? $response->getReasonPhrase();
        $this->errorCode = $this->getErrorCodeFromHttpBody();
        $this->errorLink = $this->getErrorLinkFromHttpBody();
        $this->errorType = $this->getErrorTypeFromHttpBody();

        parent::__construct($this->message, $this->httpStatus, $previous);
    }

    public function __toString()
    {
        $base = 'Meilisearch ApiException: Http Status: '.$this->httpStatus;

        if ('' !== $this->message) {
            $base .= ' - Message: '.$this->message;
        }

        if (!\is_null($this->errorCode)) {
            $base .= ' - Code: '.$this->errorCode;
        }

        if (!\is_null($this->errorType)) {
            $base .= ' - Type: '.$this->errorType;
        }

        if (!\is_null($this->errorLink)) {
            $base .= ' - Link: '.$this->errorLink;
        }

        return $base;
    }

    private function getMessageFromHttpBody(): ?string
    {
        if (\is_array($this->httpBody) && \array_key_exists('message', $this->httpBody)) {
            return $this->httpBody['message'];
        }

        return null;
    }

    private function getErrorCodeFromHttpBody(): ?string
    {
        if (\is_array($this->httpBody) && \array_key_exists('code', $this->httpBody)) {
            return $this->httpBody['code'];
        }

        return null;
    }

    private function getErrorTypeFromHttpBody(): ?string
    {
        if (\is_array($this->httpBody) && \array_key_exists('type', $this->httpBody)) {
            return $this->httpBody['type'];
        }

        return null;
    }

    private function getErrorLinkFromHttpBody(): ?string
    {
        if (\is_array($this->httpBody) && \array_key_exists('link', $this->httpBody)) {
            return $this->httpBody['link'];
        }

        return null;
    }

    public static function rethrowWithHint(\Exception $e, string $methodName)
    {
        return new \Exception(\sprintf(self::HINT_MESSAGE, $methodName), 0, $e);
    }
}
