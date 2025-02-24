<?php

declare(strict_types=1);

namespace Wearesho\RiskTools\Blacklist\Tests\Update;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Wearesho\RiskTools\Blacklist\ResponseException;
use Wearesho\RiskTools\Blacklist\Update\Factory;
use Wearesho\RiskTools\Blacklist\Category;

class FactoryTest extends TestCase
{
    private Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Factory();
    }

    public function testCreateSuccessfulResponse(): void
    {
        $response = $this->factory->createResponse([]);
        $this->assertTrue($response->isSuccessful());
    }

    #[DataProvider('errorResponseDataProvider')]
    public function testCreateResponseWithErrors(
        array $data,
        array $expectedValidation
    ): void {
        $response = $this->factory->createResponse($data);

        $this->assertFalse($response->isSuccessful());
        $this->assertEquals(count($data['errors']), $response->countErrors());

        foreach ($response->errors() as $i => $error) {
            $expected = $expectedValidation[$i];
            $record = $error->record();

            $this->assertEquals($expected['ipn'], $record->ipn());
            $this->assertEquals($expected['phone'], $record->phone());
            $this->assertEquals($expected['category'], $record->category());
            $this->assertEquals($expected['errors'], $error->errors());

            if (isset($expected['added_at'])) {
                $this->assertEquals($expected['added_at'], $record->addedAt()?->format('c'));
            } else {
                $this->assertNull($record->addedAt());
            }
        }
    }

    public static function errorResponseDataProvider(): array
    {
        return [
            'missing required fields' => [
                'data' => [
                    'errors' => [
                        [
                            'item' => [
                                'ipn' => '01234567890'
                            ],
                            'errors' => [
                                'category' => ['Missing data for required field.'],
                                'added_at' => ['Missing data for required field.']
                            ]
                        ]
                    ]
                ],
                'expectedValidation' => [
                    [
                        'ipn' => '01234567890',
                        'phone' => null,
                        'category' => Category::OTHER,
                        'added_at' => null,
                        'errors' => [
                            'category' => ['Missing data for required field.'],
                            'added_at' => ['Missing data for required field.']
                        ]
                    ]
                ]
            ],
            'invalid category' => [
                'data' => [
                    'errors' => [
                        [
                            'item' => [
                                'ipn' => '9876543210',
                                'category' => 'unsupported',
                                'added_at' => '2023-08-01T12:00:00+00:00'
                            ],
                            'errors' => [
                                'category' => ['Must be one of: military, claim, fraud, circle, dead, gaming, incapable, writeoff, inadequate, addict, lost_docs, self, other.']
                            ]
                        ]
                    ]
                ],
                'expectedValidation' => [
                    [
                        'ipn' => '9876543210',
                        'phone' => null,
                        'category' => Category::OTHER,
                        'added_at' => '2023-08-01T12:00:00+00:00',
                        'errors' => [
                            'category' => ['Must be one of: military, claim, fraud, circle, dead, gaming, incapable, writeoff, inadequate, addict, lost_docs, self, other.']
                        ]
                    ]
                ]
            ],
            'missing identifiers' => [
                'data' => [
                    'errors' => [
                        [
                            'item' => [
                                'category' => 'military',
                                'added_at' => 'XXX2023-08-01 12:00:00'
                            ],
                            'errors' => [
                                '_schema' => ['At least ipn or phone must contain valid values']
                            ]
                        ]
                    ]
                ],
                'expectedValidation' => [
                    [
                        'ipn' => null,
                        'phone' => 'invalid',
                        'category' => Category::MILITARY,
                        'added_at' => null, // invalid date format
                        'errors' => [
                            '_schema' => ['At least ipn or phone must contain valid values']
                        ]
                    ]
                ]
            ],
            'multiple errors' => [
                'data' => [
                    'errors' => [
                        [
                            'item' => [
                                'phone' => '380501234567',
                                'ipn' => '1234567890',
                                'category' => 'military',
                                'added_at' => '2023-08-01T12:00:00+00:00'
                            ],
                            'errors' => [
                                'phone' => ['Invalid phone format']
                            ]
                        ],
                        [
                            'item' => [
                                'phone' => '380507654321'
                            ],
                            'errors' => [
                                'category' => ['Missing category'],
                                'added_at' => ['Invalid date format']
                            ]
                        ]
                    ]
                ],
                'expectedValidation' => [
                    [
                        'ipn' => '1234567890',
                        'phone' => '380501234567',
                        'category' => Category::MILITARY,
                        'added_at' => '2023-08-01T12:00:00+00:00',
                        'errors' => [
                            'phone' => ['Invalid phone format']
                        ]
                    ],
                    [
                        'ipn' => null,
                        'phone' => '380507654321',
                        'category' => Category::OTHER,
                        'added_at' => null,
                        'errors' => [
                            'category' => ['Missing category'],
                            'added_at' => ['Invalid date format']
                        ]
                    ]
                ]
            ]
        ];
    }

    #[DataProvider('invalidDataProvider')]
    public function testCreateResponseWithInvalidData(
        array $data,
        string $expectedMessage
    ): void {
        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->factory->createResponse($data);
    }

    public static function invalidDataProvider(): array
    {
        return [
            'invalid errors type' => [
                ['errors' => 'invalid'],
                'Errors must be an array',
            ],
            'invalid error item' => [
                ['errors' => [['errors' => []]]],
                'Error item must be an array',
            ],
            'invalid error errors' => [
                ['errors' => [['item' => []]]],
                'Error errors must be an array',
            ],
            'error in array' => [
                ['errors' => ['not an array']],
                'Each error must be an array',
            ],
        ];
    }
}
