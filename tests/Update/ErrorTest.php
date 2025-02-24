<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests\Update;

use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\Update\{Error, Record};
use Wearesho\RiskTools\Blacklist\Category;

class ErrorTest extends TestCase
{
    private Record $record;
    private array $errors;
    private Error $error;

    protected function setUp(): void
    {
        $this->record = Record::withPhone('380501234567', Category::MILITARY);
        $this->errors = [
            'category' => ['Invalid category'],
            '_schema' => ['Schema error'],
        ];
        $this->error = new Error($this->record, $this->errors);
    }

    public function testGetters(): void
    {
        $this->assertSame($this->record, $this->error->record());
        $this->assertSame($this->errors, $this->error->errors());
    }
}
