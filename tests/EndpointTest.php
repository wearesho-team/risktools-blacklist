<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\ConfigInterface;
use Wearesho\RiskTools\Blacklist\Endpoint;

class EndpointTest extends TestCase
{
    #[DataProvider('methodProvider')]
    public function testMethod(Endpoint $endpoint, string $expectedMethod): void
    {
        $this->assertEquals($expectedMethod, $endpoint->method());
    }

    public static function methodProvider(): array
    {
        return [
            'search method' => [Endpoint::Search, 'POST'],
            'update method' => [Endpoint::Update, 'POST'],
        ];
    }

    #[DataProvider('urlProvider')]
    public function testGetUrl(Endpoint $endpoint, string $configUrl, string $expected): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config
            ->expects($this->once())
            ->method('getApiUrl')
            ->willReturn($configUrl);

        $this->assertEquals($expected, $endpoint->getUrl($config));
    }

    public static function urlProvider(): array
    {
        return [
            'search url without trailing slash' => [
                Endpoint::Search,
                'https://api.example.com',
                'https://api.example.com/blacklist/search'
            ],
            'search url with trailing slash' => [
                Endpoint::Search,
                'https://api.example.com/',
                'https://api.example.com/blacklist/search'
            ],
            'update url without trailing slash' => [
                Endpoint::Update,
                'https://api.example.com',
                'https://api.example.com/blacklist/update'
            ],
            'update url with trailing slash' => [
                Endpoint::Update,
                'https://api.example.com/',
                'https://api.example.com/blacklist/update'
            ],
        ];
    }
}
