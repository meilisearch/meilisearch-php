<?php

namespace Tests\Exception;

use MeiliSearch\Exceptions\HTTPRequestException;
use Tests\TestCase;

/**
 * Class HTTPRequestExceptionTest.
 */
class HTTPRequestExceptionTest extends TestCase
{
    public function testBadClientUrl()
    {
        try {
            $this->client->base_url = 'http://127.0.0.1.com:1234';
            $this->client->createIndex('index');
        } catch (HTTPRequestException $e) {
            $this->assertEquals(500, $e->http_status);
            $this->assertIsString($e->http_message);
            $this->assertIsString($e->http_body);
        }
    }
}
