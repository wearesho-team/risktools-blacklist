<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests\Search;

use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\Search\Response;
use Wearesho\RiskTools\Blacklist\Search\Record;

class ResponseTest extends TestCase
{
    private Response $response;
    private array $records;
    private array $partners;
    private array $categories;
    private int $found;

    protected function setUp(): void
    {
        $this->records = [
            $this->createMock(Record::class),
            $this->createMock(Record::class),
        ];
        $this->found = 2;
        $this->partners = ['01933', '87613'];
        $this->categories = ['military' => 2];

        $this->response = new Response(
            $this->records,
            $this->found,
            $this->partners,
            $this->categories
        );
    }

    public function testGetters(): void
    {
        $this->assertSame($this->records, $this->response->records());
        $this->assertEquals($this->found, $this->response->found());
        $this->assertEquals($this->partners, $this->response->partners());
        $this->assertEquals($this->categories, $this->response->categories());
    }
}
