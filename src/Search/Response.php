<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Search;

class Response
{
    /**
     * @param Record[] $records
     * @param string[] $partners
     * @param array<string, int> $categories
     */
    public function __construct(
        private readonly array $records,
        private readonly int $found,
        private readonly array $partners,
        private readonly array $categories,
    ) {
    }

    /**
     * @return Record[]
     */
    public function records(): array
    {
        return $this->records;
    }

    public function found(): int
    {
        return $this->found;
    }

    /**
     * @return string[]
     */
    public function partners(): array
    {
        return $this->partners;
    }

    /**
     * @return array<string, int>
     */
    public function categories(): array
    {
        return $this->categories;
    }
}
