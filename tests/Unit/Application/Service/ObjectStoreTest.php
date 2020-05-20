<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\ObjectStore;
use App\Domain\Model\ObjectEntry;
use App\Infrastructure\Exception\ObjectNotFoundException;
use App\Infrastructure\ObjectStorage\ObjectStorageAdapter;
use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ObjectStoreTest extends TestCase
{
    protected ObjectStore $objectStore;
    protected ObjectStorageAdapter $adapter;
    protected LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(ObjectStorageAdapter::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->objectStore = new ObjectStore($this->adapter);
        $this->objectStore->setLogger($this->logger);
    }

    public function testItPassesObjectToStorageAdapter(): void
    {
        $date = new DateTime();
        $object = new ObjectEntry('test-key', ['my-value']);
        $this->adapter->expects($this->once())
            ->method('store')
            ->with('test-key', ['my-value'], $date);
        $this->logger->expects($this->once())
            ->method('info')
            ->with('Object stored', [
                'key' => $object->getKey(),
                'value' => $object->getValue(),
                'adapter' => \get_class($this->adapter),
                'timestamp' => $date->getTimestamp(),
            ]);

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
        $this->logger->expects($this->once())
            ->method('info')
            ->with('Object fetched', [
                'key' => $object->getKey(),
                'value' => $object->getValue(),
                'adapter' => \get_class($this->adapter),
                'timestamp' => $date->getTimestamp(),
            ]);

        $this->assertEquals($object, $this->objectStore->get('test-key', $date));
    }

    public function testItReturnsNullWhenObjectIsNotFound(): void
    {
        $date = new DateTime();
        $this->adapter->expects($this->once())
            ->method('get')
            ->with('test-key', $date)
            ->willThrowException(new ObjectNotFoundException('test-key', $date));
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Object not found', [
                'key' => 'test-key',
                'adapter' => \get_class($this->adapter),
                'timestamp' => $date->getTimestamp(),
            ]);

        $this->assertNull($this->objectStore->get('test-key', $date));
    }
}
