<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\ObjectStorage;

use App\Infrastructure\ObjectStorage\AdapterProvider;
use App\Infrastructure\ObjectStorage\ObjectStorageAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Service\ServiceLocatorTrait;

class AdapterProviderTest extends TestCase
{
    public function testItGetsTheRightService(): void
    {
        $adapter1 = $this->createMock(ObjectStorageAdapter::class);
        $adapter2 = $this->createMock(ObjectStorageAdapter::class);
        $adapter3 = $this->createMock(ObjectStorageAdapter::class);
        $adapters = [
            'a1' => fn() => $adapter1,
            'a2' => fn() => $adapter2,
            'a3' => fn() => $adapter3,
        ];

        $provider = new AdapterProvider(
            new class($adapters) implements ContainerInterface {
                use ServiceLocatorTrait;
            }
        );

        $this->assertSame($adapter1, $provider('a1'));
        $this->assertSame($adapter2, $provider('a2'));
        $this->assertSame($adapter3, $provider('a3'));
        $this->expectException(NotFoundExceptionInterface::class);
        $provider('a4');
    }
}
