<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\ObjectStorage;

use App\Infrastructure\Exception\ObjectNotFoundException;
use App\Infrastructure\ObjectStorage\DoctrineObjectStorageAdapter;
use App\Infrastructure\Persistence\Entity\ObjectEntry;
use App\Infrastructure\Persistence\Repository\ObjectEntryRepository;
use PHPUnit\Framework\TestCase;

class DoctrineObjectStorageAdapterTest extends TestCase
{
    protected ObjectEntryRepository $repository;
    protected DoctrineObjectStorageAdapter $storageAdapter;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ObjectEntryRepository::class);
        $this->storageAdapter = new DoctrineObjectStorageAdapter($this->repository);
    }

    public function testItStoresTheEntryWithUtcTimezone(): void
    {
        $key = 'test-key';
        $value = ['value'];
        $date = new \DateTime('Asia/Singapore');
        $entry = new ObjectEntry();
        $entry->setKey($key);
        $entry->setValue($value);
        $entry->setCreatedAt($date);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->logicalAnd(
                $this->equalTo($entry),
                $this->callback(function (ObjectEntry $value) use ($entry) {
                    return $value->getCreatedAt()->getTimestamp() === $entry->getCreatedAt()->getTimestamp()
                        && 0 === $value->getCreatedAt()->getOffset();
                })
            ));

        $this->storageAdapter->store($key, $value, $date);
    }

    public function testItGetsTheEntryWithUtcTimezone(): void
    {
        $key = 'test-key';
        $value = ['value'];
        $date = new \DateTime('Asia/Singapore');
        $entry = new ObjectEntry();
        $entry->setKey($key);
        $entry->setValue($value);
        $entry->setCreatedAt($date);

        $this->repository->expects($this->once())
            ->method('findByKeyAtTime')
            ->with(
                $this->identicalTo($key),
                $this->callback(function (\DateTime $value) use ($date) {
                    return $value->getTimestamp() === $date->getTimestamp()
                        && 0 === $value->getOffset()
                        && $date !== $value;
                })
            )
            ->willReturn($entry);

        $this->assertSame($value, $this->storageAdapter->get($key, $date));
    }

    public function testItThrowsAnExceptionWhenNoResultsFound(): void
    {
        $key = 'test-key';
        $date = new \DateTime('Asia/Singapore');

        $this->repository->expects($this->once())
            ->method('findByKeyAtTime')
            ->with($key, $date)
            ->willReturn(null);

        $this->expectExceptionObject(new ObjectNotFoundException($key, $date));
        $this->storageAdapter->get($key, $date);
    }
}
