<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use MeiliSearch\Exceptions\ApiException;
use Tests\TestCase;

final class ApiExceptionTest extends TestCase
{
    public function testException(): void
    {
        $httpBodyExample = [
            'message' => 'This is the message',
            'errorCode' => 'this_is_the_error_code',
            'errorType' => 'this_is_the_error_type',
            'errorLink' => 'https://docs.meilisearch.com/errors',
        ];
        $statusCode = 400;

        try {
            throw new ApiException($statusCode, $httpBodyExample);
        } catch (ApiException $apiException) {
            $this->assertEquals($statusCode, $apiException->httpStatus);
            $this->assertEquals($httpBodyExample['message'],
                $apiException->message);
            $this->assertEquals($httpBodyExample['errorCode'], $apiException->errorCode);
            $this->assertEquals($httpBodyExample['errorType'], $apiException->errorType);
            $this->assertEquals($httpBodyExample['errorLink'], $apiException->errorLink);

            $expectedExceptionToString = "MeiliSearch HTTPRequestException: Http Status: {$statusCode} - Message: {$httpBodyExample['message']} - Error code: {$httpBodyExample['errorCode']} - Error type: {$httpBodyExample['errorType']} - Error link: {$httpBodyExample['errorLink']}";
            $this->assertEquals($expectedExceptionToString, (string) $apiException);
        }
    }
}
