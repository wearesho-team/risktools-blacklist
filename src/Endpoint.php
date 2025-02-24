<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist;

/**
 * @internal
 * @link https://doc.blacklist.risktools.pro/api/
 */
enum Endpoint: string
{
    case Search = '/blacklist/search';
    case Update = '/blacklist/update';

    public function method(): string
    {
        return match ($this) {
            self::Search,
            self::Update => 'POST',
        };
    }

    public function getUrl(ConfigInterface $config): string
    {
        return rtrim($config->getApiUrl(), '/') . $this->value;
    }
}
