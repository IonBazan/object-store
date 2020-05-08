<?php

declare(strict_types=1);

namespace App\Tests\Feature\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Entity\ObjectEntry;
use App\Infrastructure\Persistence\Repository\ObjectEntryRepository;
use DateTime;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ObjectEntryRepositoryTest extends KernelTestCase
{
    protected ObjectEntryRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::$container->get(ObjectEntryRepository::class);
        $this->repository->deleteAll();
    }

    public function testItCreatesRecord(): void
    {
        $entry = $this->makeEntry('my-key', ['test' => 'test2'], new DateTime());
        $this->repository->save($entry);

        $result = $this->repository->findAll();

        $this->assertSame([$entry], $result);
        $this->assertInstanceOf(Uuid::class, $entry->getId());
        $this->assertSame('my-key', $entry->getKey());
    }

    public function testItFetchesProperRecordForKeyAndDate(): void
    {
        $entries = [
            $this->makeEntry('key', 'value1', new DateTime('2020-01-01')),
            $this->makeEntry('key', 'value2', new DateTime('2020-01-02')),
            $this->makeEntry('key', 'value3', new DateTime('2020-01-03')),
            $this->makeEntry('key2', 'value0', new DateTime('2020-01-04')),
        ];

        foreach ($entries as $entry) {
            $this->repository->save($entry);
        }

        $this->assertSame($entries[0], $this->repository->findByKeyAtTime('key', new DateTime('2020-01-01')));
        $this->assertSame($entries[1], $this->repository->findByKeyAtTime('key', new DateTime('2020-01-02')));
        $this->assertSame($entries[2], $this->repository->findByKeyAtTime('key', new DateTime('2020-02-01')));
        $this->assertSame($entries[3], $this->repository->findByKeyAtTime('key2', new DateTime('2020-02-01')));
        $this->assertNull($this->repository->findByKeyAtTime('key', new DateTime('2019-01-01')));
    }

    private function makeEntry(string $key, $value, \DateTimeInterface $createdAt): ObjectEntry
    {
        $entry = new ObjectEntry();
        $entry->setKey($key);
        $entry->setValue($value);
        $entry->setCreatedAt($createdAt);

        return $entry;
    }
}
