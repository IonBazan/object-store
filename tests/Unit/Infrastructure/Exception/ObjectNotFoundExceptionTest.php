<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Exception;

use App\Infrastructure\Exception\ObjectNotFoundException;
use PHPUnit\Framework\TestCase;

class ObjectNotFoundExceptionTest extends TestCase
{
    public function testGetters(): void
    {
        $key = 'test-key';
        $date = new \DateTime();
        $exception = new ObjectNotFoundException($key, $date);
        $this->assertSame($key, $exception->getKey());
        $this->assertSame($date, $exception->getDatetime());
        $this->assertSame('Object with key "test-key" not found', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
