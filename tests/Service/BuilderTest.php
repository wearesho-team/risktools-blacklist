<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests\Service;

use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\Service\Builder;
use Wearesho\RiskTools\Blacklist\{
    Service,
    ConfigInterface,
};
use GuzzleHttp;

class BuilderTest extends TestCase
{
    private Builder $builder;
    private ConfigInterface&\PHPUnit\Framework\MockObject\MockObject $config;
    private GuzzleHttp\ClientInterface&\PHPUnit\Framework\MockObject\MockObject $httpClient;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->httpClient = $this->createMock(GuzzleHttp\ClientInterface::class);
        $this->builder = new Builder();
    }

    public function testCreate(): void
    {
        $builder = Builder::create();
        $this->assertInstanceOf(Builder::class, $builder);

        $builderWithConfig = Builder::create($this->config);
        $this->assertInstanceOf(Builder::class, $builderWithConfig);

        $builderWithAll = Builder::create($this->config, $this->httpClient);
        $this->assertInstanceOf(Builder::class, $builderWithAll);
    }

    public function testWithConfig(): void
    {
        $builder = $this->builder->withConfig($this->config);
        $this->assertSame($this->builder, $builder);
    }

    public function testWithHttpClient(): void
    {
        $builder = $this->builder->withHttpClient($this->httpClient);
        $this->assertSame($this->builder, $builder);
    }

    public function testGetServiceWithDefaults(): void
    {
        $service = $this->builder->getService();
        $this->assertInstanceOf(Service::class, $service);
    }

    public function testGetServiceWithCustomDependencies(): void
    {
        $service = $this->builder
            ->withConfig($this->config)
            ->withHttpClient($this->httpClient)
            ->getService();

        $this->assertInstanceOf(Service::class, $service);
    }
}
