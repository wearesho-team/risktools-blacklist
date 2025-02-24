<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests\Update;

use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\Update\Record;
use Wearesho\RiskTools\Blacklist\Category;
use DateTimeImmutable;

class RecordTest extends TestCase
{
    private const TEST_PHONE = '380501234567';
    private const TEST_IPN = '1234567890';
    private const TEST_DATE = '2023-01-02T14:59:04+02:00';

    private DateTimeImmutable $date;
    private Category $category;

    protected function setUp(): void
    {
        $this->date = new DateTimeImmutable(self::TEST_DATE);
        $this->category = Category::MILITARY;
    }

    public function testConstructWithoutIdentifiers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Either IPN or phone must be provided');

        // Используем рефлексию для тестирования приватного конструктора
        $reflection = new \ReflectionClass(Record::class);
        $constructor = $reflection->getConstructor();
        $constructor->setAccessible(true);
        $record = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke($record, $this->category, $this->date, null, null);
    }

    public function testPhone(): void
    {
        $record = Record::withPhone(self::TEST_PHONE, $this->category, $this->date);

        $this->assertEquals(self::TEST_PHONE, $record->phone());
        $this->assertNull($record->ipn());
        $this->assertSame($this->category, $record->category());
        $this->assertEquals($this->date, $record->addedAt());
    }

    public function testIpn(): void
    {
        $record = Record::withIpn(self::TEST_IPN, $this->category, $this->date);

        $this->assertEquals(self::TEST_IPN, $record->ipn());
        $this->assertNull($record->phone());
        $this->assertSame($this->category, $record->category());
        $this->assertEquals($this->date, $record->addedAt());
    }

    public function testPhoneAndIpn(): void
    {
        $record = Record::withPhoneAndIpn(
            self::TEST_PHONE,
            self::TEST_IPN,
            $this->category,
            $this->date
        );

        $this->assertEquals(self::TEST_PHONE, $record->phone());
        $this->assertEquals(self::TEST_IPN, $record->ipn());
        $this->assertSame($this->category, $record->category());
        $this->assertEquals($this->date, $record->addedAt());
    }

    public function testToArray(): void
    {
        $record = Record::withPhoneAndIpn(
            self::TEST_PHONE,
            self::TEST_IPN,
            $this->category,
            $this->date
        );

        $expected = [
            'phone' => self::TEST_PHONE,
            'ipn' => self::TEST_IPN,
            'category' => $this->category->value,
            'added_at' => self::TEST_DATE,
        ];

        $this->assertEquals($expected, $record->toArray());
    }

    public function testToArrayWithoutOptionalFields(): void
    {
        $record = Record::withPhone(self::TEST_PHONE, $this->category, $this->date);

        $expected = [
            'phone' => self::TEST_PHONE,
            'category' => $this->category->value,
            'added_at' => self::TEST_DATE,
        ];

        $this->assertEquals($expected, $record->toArray());
    }
}
