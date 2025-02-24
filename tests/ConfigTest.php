<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests;

use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\Config;

class ConfigTest extends TestCase
{
    private const TEST_AUTH_KEY = 'test-auth-key';
    private const TEST_API_URL = 'https://api.example.com';

    private Config $config;

    protected function setUp(): void
    {
        $this->config = new Config(
            authKey: self::TEST_AUTH_KEY,
            apiUrl: self::TEST_API_URL
        );
    }

    public function testGetAuthKey(): void
    {
        $this->assertEquals(
            self::TEST_AUTH_KEY,
            $this->config->getAuthKey()
        );
    }

    public function testGetApiUrl(): void
    {
        $this->assertEquals(
            self::TEST_API_URL,
            $this->config->getApiUrl()
        );
    }

    public function testReadonlyProperties(): void
    {
        $this->assertEquals(self::TEST_AUTH_KEY, $this->config->authKey);
        $this->assertEquals(self::TEST_API_URL, $this->config->apiUrl);
    }
}
