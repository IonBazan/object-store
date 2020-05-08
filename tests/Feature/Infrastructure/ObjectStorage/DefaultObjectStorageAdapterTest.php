<?php

declare(strict_types=1);

namespace App\Tests\Feature\Infrastructure\ObjectStorage;

use App\Infrastructure\ObjectStorage\ObjectStorageAdapter;

/**
 * Makes sure currently selected adapter exists in container and works as expected.
 */
class DefaultObjectStorageAdapterTest extends AbstractObjectStorageAdapterTest
{
    public function testAdapterExists(): void
    {
        $this->assertTrue(self::$container->has(ObjectStorageAdapter::class));
    }

    protected function getAdapterClass(): string
    {
        return ObjectStorageAdapter::class;
    }
}
