<?php

namespace Tests\Exception;

use MeiliSearch\Client;
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
            $this->client = new Client('http://127.0.0.1.com:1234', self::DEFAULT_KEY);
            $this->client->createIndex('index');
        } catch (HTTPRequestException $e) {
            $this->assertEquals(500, $e->httpStatus);
            $this->assertIsString($e->httpMessage);
            $this->assertIsString($e->httpBody);
        }
    }
}
