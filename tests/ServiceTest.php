<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Wearesho\RiskTools\Blacklist\{
    Service,
    Client,
    Endpoint,
    Category,
    Search\Factory,
    Search\Request,
    Search\Response,
};

class ServiceTest extends TestCase
{
    private Service $service;
    private Client&MockObject $client;
    private Factory&MockObject $searchFactory;
    private Request $request;
    private array $apiResponse;
    private Response&MockObject $response;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->searchFactory = $this->createMock(Factory::class);
        $this->service = new Service($this->client, $this->searchFactory);

        $this->request = Request::byPhone('380501234567', Category::MILITARY);

        $this->apiResponse = [
            'records' => [
                [
                    'phone' => '380501234567',
                    'ipn' => null,
                    'category' => 'military',
                    'added_at' => '2023-06-25T21:30:24+03:00',
                    'partner_id' => '87613',
                ],
            ],
            'found' => 1,
            'partners' => ['87613'],
            'categories' => ['military' => 1],
        ];

        $this->response = $this->createMock(Response::class);
    }

    public function testSearch(): void
    {
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo(Endpoint::Search),
                $this->equalTo($this->request->toArray())
            )
            ->willReturn($this->apiResponse);

        $this->searchFactory
            ->expects($this->once())
            ->method('createResponse')
            ->with($this->equalTo($this->apiResponse))
            ->willReturn($this->response);

        $result = $this->service->search($this->request);

        $this->assertSame($this->response, $result);
    }

    public function testSearchWithClientError(): void
    {
        $exception = new \RuntimeException('API Error');

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->searchFactory
            ->expects($this->never())
            ->method('createResponse');

        $this->expectExceptionObject($exception);

        $this->service->search($this->request);
    }

    public function testSearchWithFactoryError(): void
    {
        $exception = new \RuntimeException('Factory Error');

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturn($this->apiResponse);

        $this->searchFactory
            ->expects($this->once())
            ->method('createResponse')
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $this->service->search($this->request);
    }
}
