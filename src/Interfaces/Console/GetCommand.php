<?php

declare(strict_types=1);

namespace App\Interfaces\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class GetCommand extends AbstractObjectStoreCommand
{
    protected static $defaultName = 'app:object-store:get';

    protected function configure(): void
    {
        $this
            ->setDescription('Gets a value from the ObjectStore')
            ->addArgument('key', InputArgument::REQUIRED, 'Object key')
            ->addOption('timestamp', 't', InputOption::VALUE_REQUIRED, 'Timestamp to get the value')
        ;
    }

    public function executeInner(SymfonyStyle $io, InputInterface $input): int
    {
        $key = $input->getArgument('key');
        $timestamp = $input->getOption('timestamp');
        $time = \DateTime::createFromFormat('U', (string) $timestamp) ?: new \DateTime();

        $entry = $this->objectStore->get($key, $time);

        $io->title('Object store');

        if (!$entry) {
            $io->error('Object not found!');

            return  1;
        }

        $io->horizontalTable(['Key', 'Value', 'Timestamp'], [[
            $entry->getKey(),
            json_encode($entry->getValue()),
            $time->getTimestamp(),
        ]]);

        return 0;
    }
}
