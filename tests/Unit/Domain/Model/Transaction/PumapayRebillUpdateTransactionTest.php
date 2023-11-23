<?php

namespace Domain\Model\Transaction;

use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use Tests\UnitTestCase;

class PumapayRebillUpdateTransactionTest extends UnitTestCase
{
    /**
     * @test
     * @return RebillUpdateTransaction
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \Exception
     */
    public function it_should_create_transaction_when_previous_transaction_is_passed(): RebillUpdateTransaction
    {
        $rebill              = new Rebill(29.99, 30, 5);
        $previousTransaction = ChargeTransaction::createSingleChargeOnPumapay(
            $this->faker->uuid,
            0.30,
            PumaPayBillerSettings::PUMAPAY,
            'USD',
            $this->faker->password(30),
            $this->faker->password(24),
            $this->faker->password(300),
            'Title test',
            'Description test',
            $rebill
        );
        $rebillTransaction   = RebillUpdateTransaction::createPumapayRebillUpdateTransaction($previousTransaction);
        $this->assertInstanceOf(RebillUpdateTransaction::class, $rebillTransaction);

        return $rebillTransaction;
    }

    /**
     * @test
     *
     * @param RebillUpdateTransaction $rebillTransaction
     *
     * @throws \Exception
     * @depends it_should_create_transaction_when_previous_transaction_is_passed
     */
    public function pumapay_rebill_update_transaction_should_have_nullable_biller_settings(
        RebillUpdateTransaction $rebillTransaction
    ) {
        $rebillTransaction = RebillUpdateTransaction::createPumapayRebillUpdateTransaction($rebillTransaction);
        $this->assertNull($rebillTransaction->billerChargeSettings());
    }

    /**
     * @test
     *
     * @param RebillUpdateTransaction $rebillTransaction
     *
     * @depends it_should_create_transaction_when_previous_transaction_is_passed
     */
    public function it_should_return_a_valid_array_when_to_array_is_called(RebillUpdateTransaction $rebillTransaction)
    {
        $arrayResult = $rebillTransaction->toArray();
        $this->assertIsArray($arrayResult);
        $this->assertNull($arrayResult['billerChargeSettings']);
        $this->assertEquals((string) $rebillTransaction->transactionId(), $arrayResult['transactionId']);
    }
}