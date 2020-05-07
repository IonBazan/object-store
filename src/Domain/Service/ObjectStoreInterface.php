<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Model\ObjectEntry;

interface ObjectStoreInterface
{
    public function store(ObjectEntry $entry, \DateTime $timestamp): void;

    public function get(string $key, \DateTime $timestamp): ?ObjectEntry;
}
