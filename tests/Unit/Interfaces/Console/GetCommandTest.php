<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Console;

use App\Domain\Model\ObjectEntry;
use App\Interfaces\Console\GetCommand;
use DateTime;

class GetCommandTest extends AbstractObjectStoreCommandTest
{
    public function testItFetchesTheValue(): void
    {
        $key = 'test-key';
        $value = 'test-value';
        $time = (new DateTime())->getTimestamp();
        $this->input->expects($this->once())
            ->method('getArgument')
            ->with('key')
            ->willReturn($key);
        $this->input->expects($this->once())
            ->method('getOption')
            ->with('timestamp')
            ->willReturn($time);

        $this->objectStore->expects($this->once())
            ->method('get')
            ->with($key, $this->callback(function (DateTime $value) use ($time) {
                return $value->getTimestamp() === $time;
            }))
            ->willReturn(new ObjectEntry($key, $value));

        $this->assertSame(0, $this->command->executeInner($this->io, $this->input));
    }

    public function testItFailsWhenObjectIsNotFound(): void
    {
        $key = 'test-key';
        $this->input->expects($this->once())
            ->method('getArgument')
            ->with('key')
            ->willReturn($key);

        $this->objectStore->expects($this->once())
            ->method('get')
            ->with($key, $this->isInstanceOf(DateTime::class))
            ->willReturn(null);

        $this->assertSame(1, $this->command->executeInner($this->io, $this->input));
    }

    protected function getCommandClass(): string
    {
        return GetCommand::class;
    }
}
