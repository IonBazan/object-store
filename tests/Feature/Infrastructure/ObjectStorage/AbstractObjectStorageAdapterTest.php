<?php

declare(strict_types=1);

namespace App\Tests\Feature\Infrastructure\ObjectStorage;

use App\Infrastructure\Exception\ObjectNotFoundException;
use App\Infrastructure\ObjectStorage\ObjectStorageAdapter;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractObjectStorageAdapterTest extends KernelTestCase
{
    protected ObjectStorageAdapter $storageAdapter;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->storageAdapter = self::$container->get($this->getAdapterClass());
        $this->storageAdapter->clear();
    }

    public function testItFetchesProperRecordForKeyAndDate(): void
    {
        $entries = [
            ['key', 'value1', new DateTime('2020-01-01')],
            ['key', 'value2', new DateTime('2020-01-02')],
            ['key', 'value1', new DateTime('2020-01-03')],
            ['key', 'value3', new DateTime('2020-01-04')],
            ['key2', 'value0', new DateTime('2020-01-04')],
        ];

        foreach ($entries as $entry) {
            $this->storageAdapter->store($entry[0], $entry[1], $entry[2]);
        }

        $this->assertSame('value1', $this->storageAdapter->get('key', new DateTime('2020-01-01')));
        $this->assertSame('value2', $this->storageAdapter->get('key', new DateTime('2020-01-02')));
        $this->assertSame('value1', $this->storageAdapter->get('key', new DateTime('2020-01-03')));
        $this->assertSame('value3', $this->storageAdapter->get('key', new DateTime('2020-02-01')));
        $this->assertSame('value0', $this->storageAdapter->get('key2', new DateTime('2020-02-01')));

        $this->expectException(ObjectNotFoundException::class);
        $this->storageAdapter->get('key', new DateTime('2019-01-01'));
    }

    public function testItHandlesUnicode(): void
    {
        $entries = [
            ['🧅', 'value1', new DateTime('2020-01-01')],
            ['🐈‍⬛', 'value2', new DateTime('2020-01-01')],
        ];

        foreach ($entries as $entry) {
            $this->storageAdapter->store($entry[0], $entry[1], $entry[2]);
        }

        $this->assertSame('value1', $this->storageAdapter->get('🧅', new DateTime('2020-01-02')));
        $this->assertSame('value2', $this->storageAdapter->get('🐈‍⬛', new DateTime('2020-01-02')));
    }

    public function testItSearchCaseSensitively(): void
    {
        $entries = [
            ['key', 'value1', new DateTime('2020-01-01')],
            ['kęy', 'value2', new DateTime('2020-01-02')],
            ['KEY', 'value3', new DateTime('2020-01-03')],
        ];

        foreach ($entries as $entry) {
            $this->storageAdapter->store($entry[0], $entry[1], $entry[2]);
        }

        $this->assertSame('value1', $this->storageAdapter->get('key', new DateTime('2020-02-01')));
        $this->assertSame('value2', $this->storageAdapter->get('kęy', new DateTime('2020-02-01')));
        $this->assertSame('value3', $this->storageAdapter->get('KEY', new DateTime('2020-02-01')));
    }

    abstract protected function getAdapterClass(): string;
}
