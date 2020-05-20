<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Model\ObjectEntry;
use App\Domain\Service\ObjectStoreInterface;
use App\Infrastructure\Exception\ObjectNotFoundException;
use App\Infrastructure\ObjectStorage\ObjectStorageAdapter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ObjectStore implements ObjectStoreInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ObjectStorageAdapter $adapter;

    public function __construct(ObjectStorageAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function store(ObjectEntry $entry, \DateTime $timestamp): void
    {
        $this->adapter->store($entry->getKey(), $entry->getValue(), $timestamp);
        $this->logger->info('Object stored', [
            'key' => $entry->getKey(),
            'value' => $entry->getValue(),
            'adapter' => \get_class($this->adapter),
            'timestamp' => $timestamp->getTimestamp(),
        ]);
    }

    public function get(string $key, \DateTime $timestamp): ?ObjectEntry
    {
        try {
            $value = $this->adapter->get($key, $timestamp);
            $this->logger->info('Object fetched', [
                'key' => $key,
                'value' => $value,
                'adapter' => \get_class($this->adapter),
                'timestamp' => $timestamp->getTimestamp(),
            ]);

            return new ObjectEntry($key, $value);
        } catch (ObjectNotFoundException $e) {
            $this->logger->warning('Object not found', [
                'key' => $key,
                'adapter' => \get_class($this->adapter),
                'timestamp' => $timestamp->getTimestamp(),
            ]);

            return null;
        }
    }
}
