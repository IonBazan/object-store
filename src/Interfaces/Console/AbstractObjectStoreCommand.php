<?php

declare(strict_types=1);

namespace App\Interfaces\Console;

use App\Domain\Service\ObjectStoreInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractObjectStoreCommand extends Command
{
    protected ObjectStoreInterface $objectStore;

    public function __construct(ObjectStoreInterface $objectStore)
    {
        $this->objectStore = $objectStore;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->executeInner($io, $input);
        $io->comment('Current timestamp: '.time());

        return $result;
    }

    abstract public function executeInner(SymfonyStyle $io, InputInterface $input): int;
}
