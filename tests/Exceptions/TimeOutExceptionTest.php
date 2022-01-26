<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use MeiliSearch\Exceptions\TimeOutException;
use Tests\TestCase;

final class TimeOutExceptionTest extends TestCase
{
    public function testException(): void
    {
        $message = 'Timeout';
        $code = 502;

        try {
            throw new TimeOutException($message, $code);
        } catch (TimeOutException $timeOutException) {
            $this->assertEquals($message, $timeOutException->getMessage());
            $this->assertEquals($code, $timeOutException->getCode());

            $expectedExceptionToString = "Meilisearch TimeOutException: Code: {$code} - Message: {$message}";
            $this->assertEquals($expectedExceptionToString, (string) $timeOutException);
        }
    }

    public function testExceptionWithNullMessageAndCode(): void
    {
        $message = 'Request timed out';
        $code = 408;

        try {
            throw new TimeOutException();
        } catch (TimeOutException $timeOutException) {
            $this->assertEquals($message, $timeOutException->getMessage());
            $this->assertEquals($code, $timeOutException->getCode());

            $expectedExceptionToString = "Meilisearch TimeOutException: Code: {$code} - Message: {$message}";
            $this->assertEquals($expectedExceptionToString, (string) $timeOutException);
        }
    }

    public function testExceptionWithEmptyMessage(): void
    {
        try {
            throw new TimeOutException('');
        } catch (TimeOutException $timeOutException) {
            $this->assertEquals('', $timeOutException->getMessage());

            $expectedExceptionToString = 'Meilisearch TimeOutException: Code: 408';
            $this->assertEquals($expectedExceptionToString, (string) $timeOutException);
        }
    }
}
