<?php

declare(strict_types=1);

namespace App\Infrastructure\ObjectStorage;

use App\Infrastructure\Exception\ObjectNotFoundException;
use DateTime;
use Redis;

/**
 * This adapter stores values in Redis using following approach:
 *  - Stores following data in sorted set:
 *      key:   Key
 *      value: SHA512(Key):Timestamp
 *      score: Timestamp
 *  - Then stores the actual data as a key-value. The key is taken from value of the sorted set.
 * This is because sorted set requires uniqueness of the values which would break the history if someone changes values to:
 *  value1 -> value2 -> value1.
 */
class RedisObjectStorageAdapter implements ObjectStorageAdapter
{
    protected Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritdoc}
     */
    public function store(string $key, $data, DateTime $timestamp): void
    {
        $storageKey = sprintf('%s:%s', hash('sha512', $key), $timestamp->getTimestamp());
        $this->redis->zAdd($key, ['CH'], $timestamp->getTimestamp(), $storageKey);
        $this->redis->set($storageKey, json_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, DateTime $timestamp)
    {
        $score = (string) $timestamp->getTimestamp();
        $result = $this->redis->zRevRangeByScore($key, $score, '-inf', ['limit' => [0, 1]]);

        if (!\count($result)) {
            throw new ObjectNotFoundException($key, $timestamp);
        }

        return json_decode($this->redis->get($result[0]));
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->redis->flushDB();
    }
}
