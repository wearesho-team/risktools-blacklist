<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Search;

use Wearesho\RiskTools\Blacklist\Category;

class Request
{
    /**
     * @param Category[] $categories
     */
    private function __construct(
        private readonly ?string $phone = null,
        private readonly ?string $ipn = null,
        private readonly array $categories = [],
    ) {
        if ($phone === null && $ipn === null) {
            throw new \InvalidArgumentException('Either phone or IPN must be provided');
        }
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function ipn(): ?string
    {
        return $this->ipn;
    }

    /**
     * @return Category[]
     */
    public function categories(): array
    {
        return $this->categories;
    }

    public function toArray(): array
    {
        $data = array_filter([
            'phone' => $this->phone,
            'ipn' => $this->ipn,
        ]);

        if (!empty($this->categories)) {
            $data['categories'] = array_map(
                static fn(Category $category): string => $category->value,
                $this->categories
            );
        }

        return $data;
    }

    public static function byPhone(string $phone, Category ...$categories): self
    {
        return new self(phone: $phone, categories: $categories);
    }

    public static function byIpn(string $ipn, Category ...$categories): self
    {
        return new self(ipn: $ipn, categories: $categories);
    }

    public static function byPhoneOrIpn(
        string $phone,
        string $ipn,
        Category ...$categories
    ): self {
        return new self(
            phone: $phone,
            ipn: $ipn,
            categories: $categories
        );
    }
}
