<?php

declare(strict_types=1);

namespace App\Interfaces\Console;

use App\Domain\Model\ObjectEntry;
use DateTime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StoreCommand extends AbstractObjectStoreCommand
{
    protected static $defaultName = 'app:object-store:store';

    protected function configure(): void
    {
        $this
            ->setDescription('Stores a value in the ObjectStore')
            ->addArgument('key', InputArgument::REQUIRED, 'Object key')
            ->addArgument('value', InputArgument::REQUIRED, 'JSON-encoded value to store')
        ;
    }

    public function executeInner(SymfonyStyle $io, InputInterface $input): int
    {
        $entry = new ObjectEntry(
            $input->getArgument('key'),
            json_decode($input->getArgument('value'))
        );
        $time = new DateTime();

        $this->objectStore->store($entry, $time);

        $io->horizontalTable(['Key', 'Value', 'Timestamp'], [[
            $entry->getKey(),
            json_encode($entry->getValue()),
            $time->getTimestamp(),
        ]]);
        $io->success('Value stored successfully');

        return 0;
    }
}
