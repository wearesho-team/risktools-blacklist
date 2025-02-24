<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Service;

use Wearesho\RiskTools\Blacklist\EnvironmentConfig;
use Wearesho\RiskTools\Blacklist\ConfigInterface;
use Wearesho\RiskTools\Blacklist\Service;
use Wearesho\RiskTools\Blacklist\Search;
use Wearesho\RiskTools\Blacklist\Update;
use Wearesho\RiskTools\Blacklist\Client;
use GuzzleHttp;

class Builder
{
    public function __construct(
        private ?ConfigInterface $config = null,
        private ?GuzzleHttp\ClientInterface $httpClient = null
    ) {
    }

    public static function create(
        ?ConfigInterface $config = null,
        ?GuzzleHttp\ClientInterface $httpClient = null
    ): self {
        return new self($config, $httpClient);
    }

    public function withConfig(ConfigInterface $config): Builder
    {
        $this->config = $config;
        return $this;
    }

    public function withHttpClient(GuzzleHttp\ClientInterface $httpClient): Builder
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    public function getService(): Service
    {
        return new Service(
            new Client(
                config: $this->config ?? new EnvironmentConfig(),
                httpClient: $this->httpClient ?? new GuzzleHttp\Client(),
            ),
            new Search\Factory(),
            new Update\Factory()
        );
    }
}
