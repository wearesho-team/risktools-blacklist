<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Search;

use DateTimeImmutable;
use Wearesho\RiskTools\Blacklist\Category;

class Record
{
    public function __construct(
        private readonly ?string $ipn,
        private readonly ?string $phone,
        private readonly Category $category,
        private readonly DateTimeImmutable $addedAt,
        private readonly string $partnerId,
    ) {
    }

    public function ipn(): ?string
    {
        return $this->ipn;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function category(): Category
    {
        return $this->category;
    }

    public function addedAt(): DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function partnerId(): string
    {
        return $this->partnerId;
    }
}
