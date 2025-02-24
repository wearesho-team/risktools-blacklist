<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist;

class Config implements ConfigInterface
{
    public function __construct(
        public readonly string $authKey,
        public readonly string $apiUrl
    )
    {
    }

    public function getAuthKey(): string
    {
        return $this->authKey;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }
}
