<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\ObjectStore;
use App\Domain\Model\ObjectEntry;
use App\Infrastructure\Exception\ObjectNotFoundException;
use App\Infrastructure\ObjectStorage\ObjectStorageAdapter;
use DateTime;
use PHPUnit\Framework\TestCase;

class ObjectStoreTest extends TestCase
{
    protected ObjectStore $objectStore;
    protected ObjectStorageAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(ObjectStorageAdapter::class);
        $this->objectStore = new ObjectStore($this->adapter);
    }

    public function testItPassesObjectToStorageAdapter(): void
    {
        $date = new DateTime();
        $object = new ObjectEntry('test-key', ['my-value']);
        $this->adapter->expects($this->once())
            ->method('store')
            ->with('test-key', ['my-value'], $date);

        $this->objectStore->store($object, $date);
    }

    public function testItFetchesDataFromStorageAdapter(): void
    {
        $date = new DateTime();
        $object = new ObjectEntry('test-key', ['my-value']);
        $this->adapter->expects($this->once())
            ->method('get')
            ->with('test-key', $date)
            ->willReturn(['my-value']);

        $this->assertEquals($object, $this->objectStore->get('test-key', $date));
    }

    public function testItReturnsNullWhenObjectIsNotFound(): void
    {
        $date = new DateTime();
        $this->adapter->expects($this->once())
            ->method('get')
            ->with('test-key', $date)
            ->willThrowException(new ObjectNotFoundException('test-key', $date));

        $this->assertNull($this->objectStore->get('test-key', $date));
    }
}
