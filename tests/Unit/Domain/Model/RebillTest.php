<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use Tests\UnitTestCase;

class RebillTest extends UnitTestCase
{
    /**
     * @test
     * @return Rebill
     * @throws \Exception
     */
    public function create_should_return_rebill_when_correct_data_is_provided()
    {
        $rebill = $this->createRebill();

        $this->assertInstanceOf(Rebill::class, $rebill);

        return $rebill;
    }

    /**
     * @test
     * @depends create_should_return_rebill_when_correct_data_is_provided
     * @param Rebill $rebill Rebill object
     * @return void
     * @throws \Exception
     */
    public function rebill_should_return_true_when_equal_rebill(Rebill $rebill)
    {
        $equalRebill = $this->createRebill(
            [
                'frequency'    => $rebill->frequency(),
                'start'        => $rebill->start(),
                'rebillAmount' => $rebill->amount()->value(),
            ]
        );

        $this->assertTrue($rebill->equals($equalRebill));
    }

    /**
     * @test
     * @depends create_should_return_rebill_when_correct_data_is_provided
     * @param Rebill $rebill Rebill object
     * @return void
     * @throws \Exception
     */
    public function rebill_should_return_false_when_equal_rebill(Rebill $rebill)
    {
        $equalRebill = $this->createRebill(['frequency' => 10]);

        $this->assertFalse($rebill->equals($equalRebill));
    }

    /**
     * @group legacyService
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    public function it_should_create_rebill_from_recurring_response_payload(): void
    {
        $responsePayload = [
            "rebill_days" => 10,
            "initial_days" => 11,
            "rebill_amount" => 12
        ];

        $this->assertInstanceOf(Rebill::class, Rebill::createRebillFromLegacyResponsePayload($responsePayload));
    }

    /**
     * @group        legacyService
     * @test
     * @param array $responsePayload Response Payload
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @dataProvider dontReturnRebillProvider
     */
    public function it_should_not_create_rebill_from_response_payload(array $responsePayload): void
    {
        $this->assertNull(Rebill::createRebillFromLegacyResponsePayload($responsePayload));
    }

    /**
     * @return array
     */
    public function dontReturnRebillProvider(): array
    {
        return [
            'no_initial_days'     => [
                [
                    "rebill_days"   => 10,
                    "rebill_amount" => 12
                ]
            ],
            'no_rebill_days'      => [
                [
                    "rebill_amount" => 12,
                    "initial_days"  => 11
                ]
            ],
            'no_rebill_amount'    => [
                [
                    "initial_days" => 11,
                    "rebill_days"  => 10
                ]
            ],
            'no_recurring_data'   => [
                ["otherField" => 1]
            ],
            'no_response_payload' => [
                [null]
            ],
        ];
    }
}
