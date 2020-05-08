<?php

declare(strict_types=1);

namespace App\Tests\Feature\Infrastructure\ObjectStorage;

use App\Infrastructure\ObjectStorage\RedisObjectStorageAdapter;
use Redis;

class RedisObjectStorageAdapterTest extends AbstractObjectStorageAdapterTest
{
    protected Redis $redis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = self::$container->get('snc_redis.object_store');
    }

    protected function getAdapterClass(): string
    {
        return RedisObjectStorageAdapter::class;
    }
}
