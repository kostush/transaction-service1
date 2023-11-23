<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model\Transaction;

use DateTimeImmutable;
use Exception;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionAbortedEvent;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionApprovedEvent;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionDeclinedEvent;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\RocketGateRebillUpdateSettings;
use ProBillerNG\Transaction\Domain\Model\Status;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionCreatedEvent;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use Tests\CreateTransactionDataForNetbilling;
use Tests\UnitTestCase;

class RebillUpdateTransactionTest extends UnitTestCase
{
    use CreateTransactionDataForNetbilling;

    /**
     * @test
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public function it_should_return_a_rebill_update_transaction_when_existing_payment_info_is_provided(): RebillUpdateTransaction
    {
        $data['payment'] = [
            'method'      => 'cc',
            'information' => [
                'cardHash' => $_ENV['ROCKETGATE_CARD_HASH_1']
            ]
        ];

        $transaction = $this->createUpdateRebillTransaction($data);

        $this->assertInstanceOf(RebillUpdateTransaction::class, $transaction);

        return $transaction;
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_return_a_rebill_update_transaction_when_existing_payment_info_is_provided
     * @return void
     */
    public function it_should_have_an_id(RebillUpdateTransaction $transaction): void
    {
        $this->assertInstanceOf(TransactionId::class, $transaction->transactionId());
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_return_a_rebill_update_transaction_when_existing_payment_info_is_provided
     * @return void
     */
    public function it_should_have_a_biller_name(RebillUpdateTransaction $transaction): void
    {
        $this->assertIsString($transaction->billerName());
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_return_a_rebill_update_transaction_when_existing_payment_info_is_provided
     * @return void
     */
    public function it_should_have_biller_charge_settings(RebillUpdateTransaction $transaction): void
    {
        $this->assertInstanceOf(RocketGateRebillUpdateSettings::class, $transaction->billerChargeSettings());
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_return_a_rebill_update_transaction_when_existing_payment_info_is_provided
     * @return void
     */
    public function it_should_have_a_created_at_date(RebillUpdateTransaction $transaction): void
    {
        $this->assertInstanceOf(DateTimeImmutable::class, $transaction->createdAt());
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_return_a_rebill_update_transaction_when_existing_payment_info_is_provided
     * @return void
     */
    public function it_should_have_an_update_at_date(RebillUpdateTransaction $transaction): void
    {
        $this->assertInstanceOf(DateTimeImmutable::class, $transaction->updatedAt());
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_return_a_rebill_update_transaction_when_existing_payment_info_is_provided
     * @return RebillUpdateTransaction
     * @throws Exception
     * @throws IllegalStateTransitionException
     */
    public function it_should_have_approved_status_when_transaction_is_approved(
        RebillUpdateTransaction $transaction
    ) {
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reason_code'   => '0',
                            'response_code' => '0',
                            'reason_desc'   => 'Success'
                        ],
                        'reason'   => '0',
                        'code'     => '0',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new DateTimeImmutable()
            )
        );
        $transaction->approve();
        $this->assertEquals('approved', (string) $transaction->status());
        return $transaction;
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_have_approved_status_when_transaction_is_approved
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     */
    public function it_should_not_accept_new_declined_status(RebillUpdateTransaction $transaction): void
    {
        $this->expectException(IllegalStateTransitionException::class);
        $transaction->decline();
    }

    /**
     * @test
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public function it_should_return_a_rebill_update_transaction_when_new_payment_info_is_provided(): RebillUpdateTransaction
    {
        $data['payment'] = [
            'method'      => 'cc',
            'information' => [
                'number'          => $this->faker->creditCardNumber('Visa'),
                'expirationMonth' => (string) $this->faker->numberBetween(1, 12),
                'expirationYear'  => $this->faker->numberBetween(2025, 2030),
                'cvv'             => (string) $this->faker->numberBetween(100, 999)
            ]
        ];
        $transaction     = $this->createUpdateRebillTransaction($data);

        $this->assertInstanceOf(RebillUpdateTransaction::class, $transaction);

        return $transaction;
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_return_a_rebill_update_transaction_when_new_payment_info_is_provided
     * @return void
     */
    public function it_should_have_a_credit_card_information(RebillUpdateTransaction $transaction): void
    {
        $this->assertInstanceOf(CreditCardInformation::class, $transaction->paymentInformation());
    }

    /**
     * @test
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public function it_should_return_a_rebill_update_transaction_when_canceled_is_provided(): RebillUpdateTransaction
    {
        $transaction = $this->createCancelRebillRocketgateTransaction();

        $this->assertInstanceOf(RebillUpdateTransaction::class, $transaction);

        return $transaction;
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_return_a_rebill_update_transaction_when_canceled_is_provided
     * @return void
     */
    public function it_should_not_have_payment_information(RebillUpdateTransaction $transaction): void
    {
        $this->assertNull($transaction->paymentInformation());
    }

    /**
     * @test
     * @param RebillUpdateTransaction $transaction The Transaction object
     * @depends it_should_return_a_rebill_update_transaction_when_canceled_is_provided
     * @return void
     */
    public function it_should_not_have_charge_information(RebillUpdateTransaction $transaction): void
    {
        $this->assertNull($transaction->chargeInformation());
    }

    /**
     * @test
     * @return RebillUpdateTransaction
     * @throws Exception
     */
    public function it_should_return_a_netbilling_rebill_update_transaction_when_canceled_is_provided(): RebillUpdateTransaction
    {
        $transaction = $this->createCancelRebillNetbillingTransaction();

        $this->assertInstanceOf(RebillUpdateTransaction::class, $transaction);

        return $transaction;
    }
}
