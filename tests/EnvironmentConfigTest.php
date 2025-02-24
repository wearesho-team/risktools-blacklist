<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests;

use Horat1us\Environment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\EnvironmentConfig;

class EnvironmentConfigTest extends TestCase
{
    private EnvironmentConfig $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new EnvironmentConfig();
    }

    public function testAuthKey(): void
    {
        $testAuthKey = 'testAuthKey';
        putenv('RISK_TOOLS_BLACKLIST_AUTH_KEY=' . $testAuthKey);
        $this->assertEquals($testAuthKey, $this->config->getAuthKey());
    }

    /**
     * @return array [string, string, string][]
     */
    public static function methodDataProvider(): array
    {
        return [
            [
                'environmentKey' => 'RISK_TOOLS_BLACKLIST_AUTH_KEY',
                'testValue' => 'testAuthKey',
                'method' => fn(EnvironmentConfig $config) => $config->getAuthKey(),
            ],
            [
                'environmentKey' => 'RISK_TOOLS_BLACKLIST_API_URL',
                'testValue' => 'https://wearesho.com/risk-tools-blacklist',
                'method' => fn(EnvironmentConfig $config) => $config->getApiUrl(),
            ],
        ];
    }

    #[DataProvider('methodDataProvider')]
    public function testMethod(string $environmentKey, string $testValue, \Closure $method): void
    {
        putenv($environmentKey . '=' . $testValue);
        $this->assertEquals($testValue, $method($this->config));
        putenv($environmentKey);
        $this->expectExceptionMessage('Missing environment key ' . $environmentKey);
        $this->expectException(Environment\Exception\Missing::class);
        $method($this->config);
    }
}
