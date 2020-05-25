<?php

declare(strict_types=1);

namespace App\Infrastructure\ObjectStorage;

use Psr\Container\ContainerInterface;

class AdapterProvider
{
    private ContainerInterface $adaptersContainer;

    public function __construct(ContainerInterface $adaptersContainer)
    {
        $this->adaptersContainer = $adaptersContainer;
    }

    public function __invoke(string $name): ObjectStorageAdapter
    {
       return $this->adaptersContainer->get($name);
    }
}
