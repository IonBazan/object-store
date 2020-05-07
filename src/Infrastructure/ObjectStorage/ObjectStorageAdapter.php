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
     * @return mixed
     *
     * @throws ObjectNotFoundException
     */
    public function get(string $key, \DateTime $timestamp);
}
