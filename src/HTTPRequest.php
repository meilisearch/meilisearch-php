<?php

namespace MeiliSearch;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use MeiliSearch\Exceptions\HTTPRequestException;

class HTTPRequest
{
    public $base_url = null;
    public $api_key = null;
    public $client = null;
    public $headers = [];

    public function __construct($url, $api_key = null)
    {
        $this->base_url = $url;
        $this->api_key = $api_key;
        $this->client = new GuzzleClient();
        $this->headers = array_filter([
            'Content-type' => 'application/json',
            'X-Meili-API-Key' => $this->api_key,
        ]);
    }

    public function httpGet($path, $query = null)
    {
        $uri_query = (isset($query)) ? '?'.http_build_query($query) : '';
        $request = new GuzzleRequest(
            'GET',
            $this->base_url.$path.$uri_query,
            $this->headers,
        );

        return $this->sendRequestAndGetBody($request);
    }

    public function httpPost($path, $body = null, $query = null)
    {
        $uri_query = (isset($query)) ? '?'.http_build_query($query) : '';
        $request = new GuzzleRequest(
            'POST',
            $this->base_url.$path.$uri_query,
            $this->headers,
            json_encode($body)
        );

        return $this->sendRequestAndGetBody($request);
    }

    public function httpPut($path, $body = null, $query = null)
    {
        $uri_query = (isset($query)) ? '?'.http_build_query($query) : '';
        $request = new GuzzleRequest(
            'PUT',
            $this->base_url.$path.$uri_query,
            $this->headers,
            json_encode($body)
        );

        return $this->sendRequestAndGetBody($request);
    }

    public function httpPatch($path, $body = null)
    {
        $request = new GuzzleRequest(
            'PATCH',
            $this->base_url.$path,
            $this->headers,
            json_encode($body)
        );

        return $this->sendRequestAndGetBody($request);
    }

    public function httpDelete($path)
    {
        $request = new GuzzleRequest(
            'DELETE',
            $this->base_url.$path,
            $this->headers
        );

        return $this->sendRequestAndGetBody($request);
    }

    private function sendRequestAndGetBody($request)
    {
        try {
            $res = $this->client->send($request);

            return json_decode($res->getBody(), true);
        } catch (BadResponseException $e) {
            $status_code = $e->getResponse()->getStatusCode();
            $body = json_decode($e->getResponse()->getBody(), true);
            throw new HTTPRequestException($status_code, $body);
        }
    }
}
