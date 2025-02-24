<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\Category;
use Wearesho\RiskTools\Blacklist\Search\Request;

class RequestTest extends TestCase
{
    private const TEST_PHONE = '380501234567';
    private const TEST_IPN = '1234567890';

    #[DataProvider('validRequestDataProvider')]
    public function testValidRequest(
        \Closure $createRequest,
        ?string $expectedPhone,
        ?string $expectedIpn,
        array $expectedCategories,
        array $expectedArray
    ): void {
        $request = $createRequest();
        $this->assertEquals($expectedPhone, $request->phone());
        $this->assertEquals($expectedIpn, $request->ipn());
        $this->assertEquals($expectedCategories, $request->categories());
        $this->assertEquals($expectedArray, $request->toArray());
    }

    public static function validRequestDataProvider(): array
    {
        return [
            'phone only' => [
                fn() => Request::byPhone(self::TEST_PHONE),
                self::TEST_PHONE,
                null,
                [],
                ['phone' => self::TEST_PHONE],
            ],
            'phone with categories' => [
                fn() => Request::byPhone(
                    self::TEST_PHONE,
                    Category::FRAUD,
                    Category::GAMING
                ),
                self::TEST_PHONE,
                null,
                [Category::FRAUD, Category::GAMING],
                [
                    'phone' => self::TEST_PHONE,
                    'categories' => ['fraud', 'gaming'],
                ],
            ],
            'ipn only' => [
                fn() => Request::byIpn(self::TEST_IPN),
                null,
                self::TEST_IPN,
                [],
                ['ipn' => self::TEST_IPN],
            ],
            'ipn with categories' => [
                fn() => Request::byIpn(
                    self::TEST_IPN,
                    Category::MILITARY,
                    Category::DEAD
                ),
                null,
                self::TEST_IPN,
                [Category::MILITARY, Category::DEAD],
                [
                    'ipn' => self::TEST_IPN,
                    'categories' => ['military', 'dead'],
                ],
            ],
            'phone and ipn' => [
                fn() => Request::byPhoneOrIpn(self::TEST_PHONE, self::TEST_IPN),
                self::TEST_PHONE,
                self::TEST_IPN,
                [],
                [
                    'phone' => self::TEST_PHONE,
                    'ipn' => self::TEST_IPN,
                ],
            ],
            'phone and ipn with categories' => [
                fn() => Request::byPhoneOrIpn(
                    self::TEST_PHONE,
                    self::TEST_IPN,
                    Category::FRAUD,
                    Category::CIRCLE
                ),
                self::TEST_PHONE,
                self::TEST_IPN,
                [Category::FRAUD, Category::CIRCLE],
                [
                    'phone' => self::TEST_PHONE,
                    'ipn' => self::TEST_IPN,
                    'categories' => ['fraud', 'circle'],
                ],
            ],
        ];
    }
}
