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
    Search\Factory as SearchFactory,
    Search\Request,
    Search\Response as SearchResponse,
    Update\Factory as UpdateFactory,
    Update\Record,
    Update\Response as UpdateResponse
};
use DateTimeImmutable;

class ServiceTest extends TestCase
{
    private Service $service;
    private Client&MockObject $client;
    private SearchFactory&MockObject $searchFactory;
    private UpdateFactory&MockObject $updateFactory;
    private Request $searchRequest;
    private array $searchApiResponse;
    private SearchResponse&MockObject $searchResponse;
    private array $records;
    private array $updateApiResponse;
    private UpdateResponse&MockObject $updateResponse;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->searchFactory = $this->createMock(SearchFactory::class);
        $this->updateFactory = $this->createMock(UpdateFactory::class);

        $this->service = new Service(
            $this->client,
            $this->searchFactory,
            $this->updateFactory
        );

        $this->searchRequest = Request::byPhone('380501234567', Category::MILITARY);

        $this->searchApiResponse = [
            'records' => [
                [
                    'phone' => '380501234567',
                    'category' => 'military',
                    'added_at' => '2023-06-25T21:30:24+03:00',
                ],
            ],
        ];

        $this->searchResponse = $this->createMock(SearchResponse::class);

        $this->records = [
            Record::withPhone('380501234567', Category::MILITARY),
            Record::withIpn('1234567890', Category::FRAUD, new DateTimeImmutable()),
        ];

        $this->updateApiResponse = [
            'errors' => []
        ];

        $this->updateResponse = $this->createMock(UpdateResponse::class);
    }

    public function testSearch(): void
    {
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo(Endpoint::Search),
                $this->equalTo($this->searchRequest->toArray())
            )
            ->willReturn($this->searchApiResponse);

        $this->searchFactory
            ->expects($this->once())
            ->method('createResponse')
            ->with($this->equalTo($this->searchApiResponse))
            ->willReturn($this->searchResponse);

        $result = $this->service->search($this->searchRequest);

        $this->assertSame($this->searchResponse, $result);
    }

    public function testUpdate(): void
    {
        $expectedRequestData = [
            'records' => array_map(fn(Record $r) => $r->toArray(), $this->records)
        ];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo(Endpoint::Update),
                $this->equalTo($expectedRequestData)
            )
            ->willReturn($this->updateApiResponse);

        $this->updateFactory
            ->expects($this->once())
            ->method('createResponse')
            ->with($this->equalTo($this->updateApiResponse))
            ->willReturn($this->updateResponse);

        $result = $this->service->update($this->records);

        $this->assertSame($this->updateResponse, $result);
    }

    public function testUpdateWithClientError(): void
    {
        $exception = new \RuntimeException('API Error');

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->updateFactory
            ->expects($this->never())
            ->method('createResponse');

        $this->expectExceptionObject($exception);

        $this->service->update($this->records);
    }

    public function testUpdateWithFactoryError(): void
    {
        $exception = new \RuntimeException('Factory Error');

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturn($this->updateApiResponse);

        $this->updateFactory
            ->expects($this->once())
            ->method('createResponse')
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $this->service->update($this->records);
    }
}
