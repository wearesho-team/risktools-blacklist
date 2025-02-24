<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Update;

use DateTimeImmutable;
use Wearesho\RiskTools\Blacklist\Category;

class Record
{
    private function __construct(
        private readonly Category $category,
        private readonly DateTimeImmutable $addedAt,
        private readonly ?string $ipn = null,
        private readonly ?string $phone = null,
    ) {
        if ($ipn === null && $phone === null) {
            throw new \InvalidArgumentException(
                'Either IPN or phone must be provided'
            );
        }
    }

    public static function withPhone(
        string $phone,
        Category $category,
        DateTimeImmutable $addedAt
    ): self {
        return new self(
            category: $category,
            addedAt: $addedAt,
            phone: $phone
        );
    }

    public static function withIpn(
        string $ipn,
        Category $category,
        DateTimeImmutable $addedAt
    ): self {
        return new self(
            category: $category,
            addedAt: $addedAt,
            ipn: $ipn
        );
    }

    public static function withPhoneAndIpn(
        string $phone,
        string $ipn,
        Category $category,
        DateTimeImmutable $addedAt
    ): self {
        return new self(
            category: $category,
            addedAt: $addedAt,
            ipn: $ipn,
            phone: $phone
        );
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

    public function toArray(): array
    {
        $data = [
            'category' => $this->category->value,
            'added_at' => $this->addedAt->format('c'),
        ];

        if ($this->ipn !== null) {
            $data['ipn'] = $this->ipn;
        }

        if ($this->phone !== null) {
            $data['phone'] = $this->phone;
        }

        return $data;
    }
}
