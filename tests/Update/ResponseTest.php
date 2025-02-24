<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests\Update;

use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\Update\{Error, Response, Record};
use Wearesho\RiskTools\Blacklist\Category;

class ResponseTest extends TestCase
{
    public function testSuccessfulResponse(): void
    {
        $response = new Response();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals(0, $response->countErrors());
        $this->assertEmpty($response->errors());
    }

    public function testResponseWithErrors(): void
    {
        $record = Record::withPhone('380501234567', Category::MILITARY);
        $errors = [
            new Error($record, ['category' => ['Invalid category']]),
            new Error($record, ['_schema' => ['Schema error']]),
        ];

        $response = new Response($errors);

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(2, $response->countErrors());
        $this->assertSame($errors, $response->errors());
    }
}
