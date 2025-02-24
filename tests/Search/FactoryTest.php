<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests\Search;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\Category;
use Wearesho\RiskTools\Blacklist\Search\Factory;
use Wearesho\RiskTools\Blacklist\ResponseException;

class FactoryTest extends TestCase
{
    private Factory $factory;
    private array $validRecordData;
    private array $validResponseData;

    protected function setUp(): void
    {
        $this->factory = new Factory();

        $this->validRecordData = [
            'ipn' => '1234567890',
            'phone' => '380501234567',
            'category' => 'military',
            'added_at' => '2023-06-25T21:30:24+03:00',
            'partner_id' => '87613',
        ];

        $this->validResponseData = [
            'records' => [$this->validRecordData],
            'found' => 1,
            'partners' => ['87613'],
            'categories' => ['military' => 1],
        ];
    }

    public function testCreateRecord(): void
    {
        $record = $this->factory->createRecord($this->validRecordData);

        $this->assertEquals('1234567890', $record->ipn());
        $this->assertEquals('380501234567', $record->phone());
        $this->assertSame(Category::MILITARY, $record->category());
        $this->assertEquals(
            new DateTimeImmutable('2023-06-25T21:30:24+03:00'),
            $record->addedAt()
        );
        $this->assertEquals('87613', $record->partnerId());
    }

    public function testCreateRecordWithNullableFields(): void
    {
        $data = $this->validRecordData;
        unset($data['ipn'], $data['phone']);

        $record = $this->factory->createRecord($data);

        $this->assertNull($record->ipn());
        $this->assertNull($record->phone());
    }

    #[DataProvider('invalidRecordDataProvider')]
    public function testCreateRecordWithInvalidData(
        array $data,
        string $expectedMessage
    ): void {
        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->factory->createRecord($data);
    }

    public static function invalidRecordDataProvider(): array
    {
        return [
            'missing category' => [
                ['added_at' => '2023-06-25T21:30:24+03:00', 'partner_id' => '87613'],
                'Missing required key: category',
            ],
            'invalid category type' => [
                [
                    'category' => ['invalid'],
                    'added_at' => '2023-06-25T21:30:24+03:00',
                    'partner_id' => '87613',
                ],
                'Category must be a string',
            ],
            'invalid category value' => [
                [
                    'category' => 'invalid_category',
                    'added_at' => '2023-06-25T21:30:24+03:00',
                    'partner_id' => '87613',
                ],
                'Invalid category value: invalid_category',
            ],
            'missing added_at' => [
                ['category' => 'military', 'partner_id' => '87613'],
                'Missing required key: added_at',
            ],
            'invalid added_at' => [
                [
                    'category' => 'military',
                    'added_at' => 'invalid-date',
                    'partner_id' => '87613',
                ],
                'Failed to create Record',
            ],
            'missing partner_id' => [
                ['category' => 'military', 'added_at' => '2023-06-25T21:30:24+03:00'],
                'Missing required key: partner_id',
            ],
        ];
    }

    public function testCreateResponse(): void
    {
        $response = $this->factory->createResponse($this->validResponseData);

        $this->assertCount(1, $response->records());
        $this->assertEquals(1, $response->found());
        $this->assertEquals(['87613'], $response->partners());
        $this->assertEquals(['military' => 1], $response->categories());
    }

    #[DataProvider('invalidResponseDataProvider')]
    public function testCreateResponseWithInvalidData(
        array $data,
        string $expectedMessage
    ): void {
        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->factory->createResponse($data);
    }

    public static function invalidResponseDataProvider(): array
    {
        return [
            'missing records' => [
                ['found' => 0, 'partners' => [], 'categories' => []],
                'Missing required key: records',
            ],
            'invalid records type' => [
                ['records' => 'invalid', 'found' => 0, 'partners' => [], 'categories' => []],
                'Records must be an array',
            ],
            'missing found' => [
                ['records' => [], 'partners' => [], 'categories' => []],
                'Missing required key: found',
            ],
            'invalid found type' => [
                ['records' => [], 'found' => '0', 'partners' => [], 'categories' => []],
                'Found must be an integer',
            ],
            'missing partners' => [
                ['records' => [], 'found' => 0, 'categories' => []],
                'Missing required key: partners',
            ],
            'invalid partners type' => [
                ['records' => [], 'found' => 0, 'partners' => 'invalid', 'categories' => []],
                'Partners must be an array',
            ],
            'missing categories' => [
                ['records' => [], 'found' => 0, 'partners' => []],
                'Missing required key: categories',
            ],
            'invalid categories type' => [
                ['records' => [], 'found' => 0, 'partners' => [], 'categories' => 'invalid'],
                'Categories must be an array',
            ],
        ];
    }
}
