<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Update;

class Response
{
    /**
     * @param Error[] $errors
     */
    public function __construct(
        private readonly array $errors = [],
    ) {
    }

    public function isSuccessful(): bool
    {
        return empty($this->errors);
    }

    public function countErrors(): int
    {
        return count($this->errors);
    }

    /**
     * @return Error[]
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
