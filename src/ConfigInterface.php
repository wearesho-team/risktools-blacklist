<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist;

interface ConfigInterface
{
    public function getAuthKey(): string;
    public function getApiUrl(): string;
}
