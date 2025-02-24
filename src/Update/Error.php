<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Update;

class Error
{
    /**
     * @param array<string, string[]> $errors
     */
    public function __construct(
        private readonly Record $record,
        private readonly array $errors,
    ) {
    }

    public function record(): Record
    {
        return $this->record;
    }

    /**
     * @return array<string, string[]>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
