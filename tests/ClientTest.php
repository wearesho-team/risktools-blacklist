<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Wearesho\RiskTools\Blacklist\Client;
use Wearesho\RiskTools\Blacklist\ConfigInterface;
use Wearesho\RiskTools\Blacklist\Endpoint;
use Wearesho\RiskTools\Blacklist\RequestException;

class ClientTest extends TestCase
{
    private const TEST_AUTH_KEY = 'test-auth-key';
    private const TEST_API_URL = 'https://api.example.com';

    private Client $client;
    private MockObject&ConfigInterface $config;
    private MockObject&ClientInterface $httpClient;

    protected function setUp(): void
    {
        $this->config = $this->createMock(ConfigInterface::class);
        $this->config->method('getAuthKey')->willReturn(self::TEST_AUTH_KEY);
        $this->config->method('getApiUrl')->willReturn(self::TEST_API_URL);

        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->client = new Client($this->config, $this->httpClient);
    }

    #[DataProvider('successfulRequestDataProvider')]
    public function testSuccessfulRequest(
        Endpoint $endpoint,
        array $requestData,
        array $responseData,
        array $expectedResult
    ): void {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')
            ->willReturn(json_encode($responseData));

        $response->method('getBody')
            ->willReturn($stream);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $endpoint->method(),
                $endpoint->getUrl($this->config),
                [
                    RequestOptions::HEADERS => [
                        'AuthKey' => self::TEST_AUTH_KEY,
                    ],
                    RequestOptions::JSON => $requestData,
                ]
            )
            ->willReturn($response);

        $result = $this->client->request($endpoint, $requestData);
        $this->assertEquals($expectedResult, $result);
    }

    public static function successfulRequestDataProvider(): array
    {
        return [
            'search endpoint' => [
                Endpoint::Search,
                ['phone' => '380501234567'],
                [
                    'status' => 'ok',
                    'result' => ['found' => false]
                ],
                ['found' => false],
            ],
            'update endpoint' => [
                Endpoint::Update,
                ['phone' => '380501234567', 'type' => 'spam'],
                [
                    'status' => 'ok',
                    'result' => ['updated' => true]
                ],
                ['updated' => true],
            ],
        ];
    }

    #[DataProvider('failedRequestDataProvider')]
    public function testFailedRequest(
        Endpoint $endpoint,
        array $requestData,
        \Exception $exception,
        string $expectedMessage
    ): void {
        $this->httpClient
            ->method('request')
            ->willThrowException($exception);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->client->request($endpoint, $requestData);
    }

    public static function failedRequestDataProvider(): array
    {
        return [
            'network error' => [
                Endpoint::Search,
                ['phone' => '380501234567'],
                new GuzzleRequestException(
                    'Network error',
                    new \GuzzleHttp\Psr7\Request('POST', 'test')
                ),
                'Request to endpoint /blacklist/search failed: Network error',
            ],
        ];
    }

    #[DataProvider('invalidResponseDataProvider')]
    public function testInvalidResponseFormat(
        Endpoint $endpoint,
        array $requestData,
        string $responseBody,
        string $expectedMessage
    ): void {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')
            ->willReturn($responseBody);

        $response->method('getBody')
            ->willReturn($stream);

        $this->httpClient
            ->method('request')
            ->willReturn($response);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->client->request($endpoint, $requestData);
    }

    public static function invalidResponseDataProvider(): array
    {
        return [
            'invalid json' => [
                Endpoint::Search,
                ['phone' => '380501234567'],
                'invalid json',
                'Failed to parse JSON for endpoint /blacklist/search: Syntax error',
            ],
            'missing status' => [
                Endpoint::Search,
                ['phone' => '380501234567'],
                json_encode(['result' => []]),
                'Missing response status for endpoint /blacklist/search',
            ],
            'missing result' => [
                Endpoint::Search,
                ['phone' => '380501234567'],
                json_encode(['status' => 'ok']),
                'Missing response result for endpoint /blacklist/search',
            ],
            'invalid result type' => [
                Endpoint::Search,
                ['phone' => '380501234567'],
                json_encode(['status' => 'ok', 'result' => 'not an array']),
                'Response result must be an array for endpoint /blacklist/search',
            ],
            'unsuccessful status' => [
                Endpoint::Search,
                ['phone' => '380501234567'],
                json_encode(['status' => 'error', 'result' => []]),
                'Unsuccessful response for endpoint /blacklist/search',
            ],
        ];
    }
}
