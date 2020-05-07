<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Console;

use App\Domain\Service\ObjectStoreInterface;
use App\Interfaces\Console\AbstractObjectStoreCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractObjectStoreCommandTest extends TestCase
{
    /** @var SymfonyStyle&MockObject */
    protected SymfonyStyle $io;
    /** @var InputInterface&MockObject */
    protected InputInterface $input;
    /** @var ObjectStoreInterface&MockObject */
    protected ObjectStoreInterface $objectStore;
    protected AbstractObjectStoreCommand $command;

    protected function setUp(): void
    {
        $this->io = $this->createMock(SymfonyStyle::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->objectStore = $this->createMock(ObjectStoreInterface::class);
        $commandClass = $this->getCommandClass();
        $this->command = new $commandClass($this->objectStore);
    }

    abstract protected function getCommandClass(): string;
}
