<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Model;

use Exception;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyPostbackBillerResponse;
use Tests\UnitTestCase;

/**
 * @group   legacyService
 * Class LegacyPostbackBillerResponseTest
 * @package Tests\Unit\Infrastructure\Domain\Model
 */
class LegacyPostbackBillerResponseTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_return_approved_response_when_status_code_is_zero(): void
    {
        $legacyResponse = LegacyPostbackBillerResponse::create(
            [
                'data' => [
                    'subscriptionId' => 1,
                    'transactionId'  => 2,
                    'memberDetails'  => [
                        'member_id' => 3
                    ]
                ]
            ],
            'postback',
            LegacyPostbackBillerResponse::APPROVED_STATUS_CODE
        );
        $this->assertTrue($legacyResponse->approved());
    }

    /**
     * @test
     * @param int $statusCode Status Code
     * @return void
     * @dataProvider statusCodeProvider
     * @throws Exception
     */
    public function it_should_return_declined_response_when_status_code_is_different_than_zero(int $statusCode): void
    {
        $legacyResponse = LegacyPostbackBillerResponse::create([
            'data' => [
                'subscriptionId' => 1,
                'transactionId'  => 2,
                'memberDetails'  => [
                    'member_id' => 3
                ]
            ]
        ], 'postback', $statusCode);
        $this->assertTrue($legacyResponse->declined());
    }

    /**
     * @return array
     */
    public function statusCodeProvider(): array
    {
        return [
            'statusCode_as_1'    => [1],
            'statusCode_as_2'    => [2],
            'statusCode_as_1234' => [1234],
        ];
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_be_cross_sale_when_main_product_on_custom_field_is_diferent_of_product_id_in_the_payload(
    ): void
    {
        $payload = [
            'data'   => [
                'productId'      => 2,
                'subscriptionId' => 1,
                'transactionId'  => 2,
                'memberDetails'  => [
                    'member_id' => 3
                ]
            ],
            'custom' => [
                'mainProductId' => 1
            ]
        ];

        $legacyResponse = LegacyPostbackBillerResponse::create(
            $payload,
            'postback',
            LegacyPostbackBillerResponse::APPROVED_STATUS_CODE
        );
        $this->assertTrue($legacyResponse->isCrossSale());
    }

    /**
     * @test
     * @param array $payload Payload
     * @return void
     * @throws Exception
     * @dataProvider notCrossSaleProvider
     */
    public function it_should_not_be_cross_sale(array $payload): void
    {
        $legacyResponse = LegacyPostbackBillerResponse::create(
            $payload,
            'postback',
            LegacyPostbackBillerResponse::APPROVED_STATUS_CODE
        );
        $this->assertFalse($legacyResponse->isCrossSale());
    }

    /**
     * @return array
     */
    public function notCrossSaleProvider(): array
    {
        return [
            'same_product_id'                    => [
                [
                    'data' => [
                        'productId'      => 1,
                        'subscriptionId' => 1,
                        'transactionId'  => 2,
                        'memberDetails'  => [
                            'member_id' => 3
                        ]
                    ]
                ],
                'custom' => [
                    'mainProductId' => 1
                ]
            ],
            'no_custom_field'                    => [
                [
                    'data' => [
                        'productId'      => 1,
                        'subscriptionId' => 1,
                        'transactionId'  => 2,
                        'memberDetails'  => [
                            'member_id' => 3
                        ]
                    ],
                ]
            ],
            'no_main_product_id_on_custom_field' => [
                [
                    'data'   => [
                        'productId'      => 1,
                        'subscriptionId' => 1,
                        'transactionId'  => 2,
                        'memberDetails'  => [
                            'member_id' => 3
                        ]
                    ],
                    'custom' => [
                        'otherField' => 1
                    ]
                ]
            ],
        ];
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_have_amount_when_there_is_a_settle_amount_on_response_payload(): void
    {
        $amount          = $this->faker->randomFloat();
        $responsePayload = [
            'settleAmount' => $amount,
            'data'         => [
                'productId'      => 1,
                'subscriptionId' => 1,
                'transactionId'  => 2,
                'memberDetails'  => [
                    'member_id' => 3
                ]
            ],
            'custom'       => [
                'otherField' => 1
            ]
        ];
        $legacyResponse  = LegacyPostbackBillerResponse::create(
            $responsePayload,
            'postback',
            LegacyPostbackBillerResponse::APPROVED_STATUS_CODE
        );
        $this->assertEquals($amount, $legacyResponse->amount()->value());
    }

    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_have_rebill_when_there_is_recurring_field_inside_subscription(): void
    {
        $rebillAmount = $this->faker->randomFloat();
        $rebillDays   = $this->faker->randomNumber();

        $payload = [
            'data' => [
                'subscriptionId'         => 1,
                'transactionId'          => 2,
                'memberDetails'          => [
                    'member_id' => 3
                ],
                'allMemberSubscriptions' => [
                    1 => [
                        'rebill_amount' => $rebillAmount,
                        'rebill_days'   => $rebillDays,
                        "initial_days"  => '30',
                    ],
                ]
            ]
        ];

        $legacyResponse = LegacyPostbackBillerResponse::create(
            $payload,
            'postback',
            LegacyPostbackBillerResponse::APPROVED_STATUS_CODE
        );
        $this->assertEquals($rebillAmount, $legacyResponse->rebill()->amount()->value());
        $this->assertEquals($rebillDays, $legacyResponse->rebill()->frequency());
    }

    /**
     * @test
     * @param array $payload Payload
     * @return void
     * @throws Exception
     * @dataProvider noRebillProvider
     */
    public function it_should_not_have_rebill(array $payload): void
    {
        $legacyResponse = LegacyPostbackBillerResponse::create(
            $payload,
            'postback',
            LegacyPostbackBillerResponse::APPROVED_STATUS_CODE
        );
        $this->assertNull($legacyResponse->rebill());
    }

    /**
     * @return array
     */
    public function noRebillProvider(): array
    {
        return [
            'no_recurring_fields'         => [[
                'data' => [
                    'subscriptionId'         => 1,
                    'transactionId'          => 2,
                    'memberDetails'          => [
                        'member_id' => 3
                    ],
                    'allMemberSubscriptions' => [
                        1 => [
                            'otherField' => 1
                        ]
                    ]
                ]
            ]],
            'no_all_member_subscriptions' => [[
                'data' => [
                    'subscriptionId' => 1,
                    'transactionId'  => 2,
                    'memberDetails'  => [
                        'member_id' => 3
                    ],
                ]
            ]],
            'subscription_not_found'      => [[
                'data' => [
                    'subscriptionId'         => 1,
                    'transactionId'          => 2,
                    'memberDetails'          => [
                        'member_id' => 3
                    ],
                    'allMemberSubscriptions' => [
                        2 => [
                            'recurring' => [
                                "rebillAmount"   => 10,
                                "rebillDays"     => 12,
                                "created"        => "2020-08-19 16:19:14",
                                "nextRebillDate" => "2020-11-17 16:19:14",
                            ]
                        ]
                    ]
                ]
            ],
        ]];
    }
}
