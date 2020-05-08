<?php

declare(strict_types=1);

namespace App\Infrastructure\ObjectStorage;

use App\Infrastructure\Exception\ObjectNotFoundException;

interface ObjectStorageAdapter
{
    /**
     * @param mixed $data
     */
    public function store(string $key, $data, \DateTime $timestamp): void;

    /**
     * @throws ObjectNotFoundException
     *
     * @return mixed
     */
    public function get(string $key, \DateTime $timestamp);

    /**
     * Clears the data in the storage.
     */
    public function clear(): void;
}
