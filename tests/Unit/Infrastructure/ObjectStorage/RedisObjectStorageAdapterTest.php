<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\ObjectStorage;

use App\Infrastructure\Exception\ObjectNotFoundException;
use App\Infrastructure\ObjectStorage\RedisObjectStorageAdapter;
use PHPUnit\Framework\TestCase;
use Redis;

class RedisObjectStorageAdapterTest extends TestCase
{
    protected Redis $redis;
    protected RedisObjectStorageAdapter $storageAdapter;

    protected function setUp(): void
    {
        $this->redis = $this->createMock(Redis::class);
        $this->storageAdapter = new RedisObjectStorageAdapter($this->redis);
    }

    public function testItStoresTheEntryWithTimestampAsScore(): void
    {
        $key = 'test-key';
        $value = ['value'];
        $date = new \DateTime('Asia/Singapore');
        $storageKey = sprintf('%s:%s', hash('sha512', $key), $date->getTimestamp());

        $this->redis->expects($this->once())
            ->method('zAdd')
            ->with($key, ['CH'], $date->getTimestamp(), $storageKey);
        $this->redis->expects($this->once())
            ->method('set')
            ->with($storageKey, json_encode($value));

        $this->storageAdapter->store($key, $value, $date);
    }

    public function testItGetsTheSingleEntryUsingScore(): void
    {
        $key = 'test-key';
        $value = ['value'];
        $date = new \DateTime('Asia/Singapore');

        $this->redis->expects($this->once())
            ->method('zRevRangeByScore')
            ->with($key, $date->getTimestamp(), '-inf', ['limit' => [0, 1]])
            ->willReturn([sprintf('%s:%s', hash('sha512', $key), $date->getTimestamp())]);

        $this->redis->expects($this->once())
            ->method('get')
            ->with(sprintf('%s:%s', hash('sha512', $key), $date->getTimestamp()))
            ->willReturn(json_encode($value));

        $this->assertSame($value, $this->storageAdapter->get($key, $date));
    }

    public function testItThrowsAnExceptionWhenNoResultsFound(): void
    {
        $key = 'test-key';
        $date = new \DateTime('Asia/Singapore');

        $this->redis->expects($this->once())
            ->method('zRevRangeByScore')
            ->with($key, $date->getTimestamp(), '-inf', ['limit' => [0, 1]])
            ->willReturn([]);

        $this->expectExceptionObject(new ObjectNotFoundException($key, $date));
        $this->storageAdapter->get($key, $date);
    }
}
