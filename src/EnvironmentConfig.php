<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist;

use Horat1us\Environment;

class EnvironmentConfig extends Environment\Config implements ConfigInterface
{
    public function __construct(string $keyPrefix = 'RISK_TOOLS_BLACKLIST_')
    {
        parent::__construct($keyPrefix);
    }

    public function getAuthKey(): string
    {
        return $this->getEnv("AUTH_KEY");
    }

    public function getApiUrl(): string
    {
        return $this->getEnv("API_URL");
    }
}
