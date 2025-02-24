<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests\Search;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\Category;
use Wearesho\RiskTools\Blacklist\Search\Record;

class RecordTest extends TestCase
{
    private const TEST_IPN = '1234567890';
    private const TEST_PHONE = '380501234567';
    private const TEST_ADDED_AT = '2023-06-25T21:30:24+03:00';
    private const TEST_PARTNER_ID = '87613';

    private Record $record;
    private DateTimeImmutable $addedAt;
    private Category $category;

    protected function setUp(): void
    {
        $this->addedAt = new DateTimeImmutable(self::TEST_ADDED_AT);
        $this->category = Category::MILITARY;
        $this->record = new Record(
            self::TEST_IPN,
            self::TEST_PHONE,
            $this->category,
            $this->addedAt,
            self::TEST_PARTNER_ID
        );
    }

    public function testGetters(): void
    {
        $this->assertEquals(self::TEST_IPN, $this->record->ipn());
        $this->assertEquals(self::TEST_PHONE, $this->record->phone());
        $this->assertSame($this->category, $this->record->category());
        $this->assertEquals($this->addedAt, $this->record->addedAt());
        $this->assertEquals(self::TEST_PARTNER_ID, $this->record->partnerId());
    }

    public function testNullableFields(): void
    {
        $record = new Record(
            null,
            null,
            $this->category,
            $this->addedAt,
            self::TEST_PARTNER_ID
        );

        $this->assertNull($record->ipn());
        $this->assertNull($record->phone());
    }
}
