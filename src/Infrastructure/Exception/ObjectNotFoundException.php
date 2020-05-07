<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

class ObjectNotFoundException extends \Exception
{
    protected string $key;
    protected \DateTime $datetime;

    public function __construct(string $key, \DateTime $datetime)
    {
        $this->key = $key;
        $this->datetime = $datetime;

        parent::__construct(sprintf('Object with key "%s" not found', $key));
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }
}
