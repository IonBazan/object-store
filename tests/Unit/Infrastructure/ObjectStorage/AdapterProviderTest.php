<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\ObjectStorage;

use App\Infrastructure\ObjectStorage\AdapterProvider;
use App\Infrastructure\ObjectStorage\ObjectStorageAdapter;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class AdapterProviderTest extends TestCase
{
    public function testItGetsTheRightService(): void
    {
        $adapter1 = $this->createMock(ObjectStorageAdapter::class);
        $adapter2 = $this->createMock(ObjectStorageAdapter::class);
        $adapter3 = $this->createMock(ObjectStorageAdapter::class);
        $adapters = new \ArrayIterator(['a1' => $adapter1, 'a2' => $adapter2, 'a3' => $adapter3]);

        $provider = new AdapterProvider($adapters, 'a1');

        $this->assertSame($adapter1, $provider());
        $this->assertSame($adapter1, $provider('a1'));
        $this->assertSame($adapter2, $provider('a2'));
        $this->assertSame($adapter3, $provider('a3'));
        $this->expectException(UnexpectedValueException::class);
        $provider('a4');
    }
}
