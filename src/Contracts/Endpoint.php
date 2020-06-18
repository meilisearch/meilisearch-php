<?php


namespace MeiliSearch\Contracts;


use MeiliSearch\Exceptions\HTTPRequestException;
use Psr\Http\Message\ResponseInterface;

abstract class Endpoint
{
    /**
     * @param ResponseInterface $response
     * @return array
     * @throws HTTPRequestException@
     */
    public function parseResponse(ResponseInterface $response): array
    {
        if ($response->getStatusCode() >= 300) {
            $body = json_decode($response->getBody()->getContents(), true);
            throw new HTTPRequestException($response->getStatusCode(), $body);
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}