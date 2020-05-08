<?php

declare(strict_types=1);

namespace App\Infrastructure\ObjectStorage;

use App\Infrastructure\Exception\ObjectNotFoundException;
use App\Infrastructure\Persistence\Entity\ObjectEntry as EntryEntity;
use App\Infrastructure\Persistence\Repository\ObjectEntryRepository;
use DateTime;
use DateTimeZone;

class DoctrineObjectStorageAdapter implements ObjectStorageAdapter
{
    protected ObjectEntryRepository $objectEntryRepository;

    public function __construct(ObjectEntryRepository $objectEntryRepository)
    {
        $this->objectEntryRepository = $objectEntryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function store(string $key, $data, DateTime $timestamp): void
    {
        $objectEntry = new EntryEntity();
        $objectEntry->setKey($key);
        $objectEntry->setValue($data);
        $objectEntry->setCreatedAt($this->toUTC($timestamp));

        $this->objectEntryRepository->save($objectEntry);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, DateTime $timestamp)
    {
        $entry = $this->objectEntryRepository->findByKeyAtTime($key, $this->toUTC($timestamp));

        if (!$entry) {
            throw new ObjectNotFoundException($key, $timestamp);
        }

        return $entry->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->objectEntryRepository->deleteAll();
    }

    private function toUTC(DateTime $dateTime): DateTime
    {
        return (clone $dateTime)->setTimezone(new DateTimeZone('UTC'));
    }
}
