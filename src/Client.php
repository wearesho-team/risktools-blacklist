<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist;

use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class Client
{
    private const HEADER_AUTH_KEY = 'AuthKey';
    private const RESPONSE_KEY_STATUS = 'status';
    private const RESPONSE_KEY_RESULT = 'result';
    private const RESPONSE_STATUS_OK = 'ok';

    public function __construct(
        private readonly ConfigInterface            $config,
        private readonly GuzzleHttp\ClientInterface $httpClient
    )
    {
    }

    /**
     * @throws RequestException
     */
    public function request(Endpoint $endpoint, array $data): array
    {
        $responseData = $this->parse(
            $endpoint,
            $this->makeRequest($endpoint, $data)
        );
        $this->validateResponse($endpoint, $responseData);
        return $responseData[self::RESPONSE_KEY_RESULT];
    }

    private function makeRequest(Endpoint $endpoint, array $data): ResponseInterface
    {
        try {
            return $this->httpClient->request(
                $endpoint->method(),
                $endpoint->getUrl($this->config),
                [
                    GuzzleHttp\RequestOptions::HEADERS => [
                        self::HEADER_AUTH_KEY => $this->config->getAuthKey(),
                    ],
                    GuzzleHttp\RequestOptions::JSON => $data,
                ]
            );
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            throw new RequestException(
                "Request to endpoint {$endpoint->value} failed: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    private function parse(Endpoint $endpoint, ResponseInterface $response): array
    {
        try {
            $responseData = json_decode(
                $response->getBody()->__toString(),
                true,
                32,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            throw new RequestException(
                "Failed to parse JSON for endpoint {$endpoint->value}: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        return $responseData;
    }

    private function validateResponse(Endpoint $endpoint, array &$responseData): void
    {
        if (!array_key_exists(static::RESPONSE_KEY_STATUS, $responseData)) {
            throw new RequestException("Missing response status for endpoint {$endpoint->value}");
        }
        if (!array_key_exists(static::RESPONSE_KEY_RESULT, $responseData)) {
            throw new RequestException("Missing response result for endpoint {$endpoint->value}");
        }
        if (!is_array($responseData[static::RESPONSE_KEY_RESULT])) {
            throw new RequestException("Response result must be an array for endpoint {$endpoint->value}");
        }
        if ($responseData[static::RESPONSE_KEY_STATUS] !== static::RESPONSE_STATUS_OK) {
            throw new RequestException(
                "Unsuccessful response for endpoint {$endpoint->value}"
            );
        }
    }
}
