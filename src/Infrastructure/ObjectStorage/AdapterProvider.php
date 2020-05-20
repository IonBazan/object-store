<?php

declare(strict_types=1);

namespace App\Infrastructure\ObjectStorage;

class AdapterProvider
{
    /** @var ObjectStorageAdapter[] */
    private iterable $adapters;
    private string $defaultAdapter;

    /**
     * @param ObjectStorageAdapter[] $adapters
     */
    public function __construct(iterable $adapters, string $defaultAdapter)
    {
        $this->adapters = $adapters;
        $this->defaultAdapter = $defaultAdapter;
    }

    public function __invoke(?string $name = null): ObjectStorageAdapter
    {
        $name ??= $this->defaultAdapter;
        $adapters = iterator_to_array($this->adapters);

        if (!\array_key_exists($name, $adapters)) {
            throw new \UnexpectedValueException(sprintf('Invalid adapter name "%s" provided. Available options are: %s', $name, implode(', ', array_keys($adapters))));
        }

        return $adapters[$name];
    }
}
