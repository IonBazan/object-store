<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Model\ObjectEntry;
use App\Domain\Service\ObjectStoreInterface;
use App\Infrastructure\Exception\ObjectNotFoundException;
use App\Infrastructure\ObjectStorage\ObjectStorageAdapter;

class ObjectStore implements ObjectStoreInterface
{
    private ObjectStorageAdapter $adapter;

    public function __construct(ObjectStorageAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function store(ObjectEntry $entry, \DateTime $timestamp): void
    {
        $this->adapter->store($entry->getKey(), $entry->getValue(), $timestamp);
    }

    public function get(string $key, \DateTime $timestamp): ?ObjectEntry
    {
        try {
            return new ObjectEntry($key, $this->adapter->get($key, $timestamp));
        } catch (ObjectNotFoundException $e) {
            return null;
        }
    }
}
