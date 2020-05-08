<?php

declare(strict_types=1);

namespace App\Tests\Feature\Infrastructure\ObjectStorage;

use App\Infrastructure\ObjectStorage\DoctrineObjectStorageAdapter;

class DoctrineObjectStorageAdapterTest extends AbstractObjectStorageAdapterTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getAdapterClass(): string
    {
        return DoctrineObjectStorageAdapter::class;
    }
}
