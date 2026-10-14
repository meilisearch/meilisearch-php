<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Meilisearch\Exceptions\TimeoutException;
use Tests\TestCase;

final class TimeOutExceptionTest extends TestCase
{
    public function testException(): void
    {
        $message = 'Timeout';
        $code = 502;

        try {
            throw new TimeoutException($message, $code);
        } catch (TimeoutException $timeoutException) {
            self::assertSame($message, $timeoutException->getMessage());
            self::assertSame($code, $timeoutException->getCode());

            $expectedExceptionToString = "Meilisearch TimeoutException: Code: {$code} - Message: {$message}";
            self::assertSame($expectedExceptionToString, (string) $timeoutException);
        }
    }

    public function testExceptionWithNullMessageAndCode(): void
    {
        $message = 'Request timed out';
        $code = 408;

        try {
            throw new TimeoutException();
        } catch (TimeoutException $timeoutException) {
            self::assertSame($message, $timeoutException->getMessage());
            self::assertSame($code, $timeoutException->getCode());

            $expectedExceptionToString = "Meilisearch TimeoutException: Code: {$code} - Message: {$message}";
            self::assertSame($expectedExceptionToString, (string) $timeoutException);
        }
    }

    public function testExceptionWithEmptyMessage(): void
    {
        try {
            throw new TimeoutException('');
        } catch (TimeoutException $timeoutException) {
            self::assertSame('', $timeoutException->getMessage());

            $expectedExceptionToString = 'Meilisearch TimeoutException: Code: 408';
            self::assertSame($expectedExceptionToString, (string) $timeoutException);
        }
    }
}
