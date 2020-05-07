<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Console;

use App\Domain\Model\ObjectEntry;
use App\Interfaces\Console\StoreCommand;
use DateTime;

class StoreCommandTest extends AbstractObjectStoreCommandTest
{
    public function testItStoresTheValue(): void
    {
        $key = 'test-key';
        $value = 'test-value';
        $this->input->expects($this->exactly(2))
            ->method('getArgument')
            ->withConsecutive(['key'], ['value'])
            ->willReturnOnConsecutiveCalls($key, json_encode($value));

        $this->objectStore->expects($this->once())
            ->method('store')
            ->with(new ObjectEntry($key, $value), $this->isInstanceOf(DateTime::class));

        $this->assertSame(0, $this->command->executeInner($this->io, $this->input));
    }

    protected function getCommandClass(): string
    {
        return StoreCommand::class;
    }
}
