<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model\Transaction;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\EpochBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidThreedsVersionException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionAbortedEvent;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionApprovedEvent;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionDeclinedEvent;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\PaymentInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Status;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionCreatedEvent;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use Tests\UnitTestCase;

class ChargeTransactionTest extends UnitTestCase
{
    /**
     * @test
     * @return ChargeTransaction
     * @throws \Exception
     */
    public function create_should_return_a_transaction(): ChargeTransaction
    {

        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $this->assertInstanceOf(ChargeTransaction::class, $transaction);

        return $transaction;
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_should_return_a_transaction
     * @return void
     */
    public function created_transaction_should_have_an_id(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(TransactionId::class, $transaction->transactionId());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_should_return_a_transaction
     * @return void
     */
    public function created_transaction_should_have_a_biller_name(ChargeTransaction $transaction): void
    {
        $this->assertIsString($transaction->billerName());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_should_return_a_transaction
     * @return void
     */
    public function created_transaction_should_have_payment_information(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(PaymentInformation::class, $transaction->paymentInformation());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_should_return_a_transaction
     * @return void
     */
    public function created_transaction_should_have_charge_information(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(ChargeInformation::class, $transaction->chargeInformation());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_should_return_a_transaction
     * @return void
     */
    public function created_transaction_should_have_biller_charge_settings(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(RocketGateChargeSettings::class, $transaction->billerChargeSettings());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_should_return_a_transaction
     * @return void
     */
    public function created_transaction_should_have_a_created_at_date(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $transaction->createdAt());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_should_return_a_transaction
     * @return void
     */
    public function created_transaction_should_have_an_update_at_date(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $transaction->updatedAt());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @return ChargeTransaction
     * @depends create_should_return_a_transaction
     */
    public function created_transaction_should_have_a_status(ChargeTransaction $transaction): ChargeTransaction
    {
        $this->assertInstanceOf(Status::class, $transaction->status());

        return $transaction;
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends created_transaction_should_have_a_status
     * @return void
     */
    public function created_transaction_should_have_a_pending_status(ChargeTransaction $transaction): void
    {
        $this->assertEquals('pending', (string) $transaction->status());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidThreedsVersionException
     * @depends create_should_return_a_transaction
     */
    public function created_transaction_should_have_3ds_version_set_when_update(ChargeTransaction $transaction): ChargeTransaction
    {
        $transaction->updateThreedsVersion(1);

        $this->assertEquals(1, $transaction->threedsVersion());

        return $transaction;
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @throws Exception
     * @throws InvalidThreedsVersionException
     * @depends created_transaction_should_have_3ds_version_set_when_update
     * @return void
     */
    public function created_transaction_should_throw_exception_when_update(ChargeTransaction $transaction)
    {
        $this->expectException(InvalidThreedsVersionException::class);
        $transaction->updateThreedsVersion(2);
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_should_return_a_transaction
     * @return void
     */
    public function created_transaction_should_not_have_biller_interactions(ChargeTransaction $transaction): void
    {
        $this->assertEquals(0, $transaction->billerInteractions()->count());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_not_use_threed(ChargeTransaction $transaction): void
    {
        $this->assertFalse($transaction->requiredToUse3D());
    }

    /**
     * @test
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     * @return void
     */
    public function created_new_credit_card_transaction_should_use_threed(): void
    {
        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard(['useThreeD' => true]);

        $this->assertTrue($transaction->requiredToUse3D());
    }

    /**
     * @test
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     * @return void
     */
    public function it_should_have_new_credit_card_threed_transaction_with_failed_threed_response(): void
    {
        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard(['useThreeD' => true]);

        $transaction->updateTransactionWith3D(false);

        $this->assertFalse($transaction->with3D());
    }

    /**
     * @test
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     * @return void
     */
    public function it_should_have_new_credit_card_threed_transaction_with_success_threed_response(): void
    {
        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard(['useThreeD' => true]);

        $this->assertTrue($transaction->with3D());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends created_transaction_should_have_a_status
     * @throws IllegalStateTransitionException
     * @throws \Exception
     * @return ChargeTransaction
     */
    public function created_transaction_with_pending_status_should_accept_new_approved_status(ChargeTransaction $transaction): ChargeTransaction
    {
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json"],
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
                new \DateTimeImmutable()
            )
        );
        $transaction->approve();
        $this->assertEquals('approved', (string) $transaction->status());

        return $transaction;
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends created_transaction_with_pending_status_should_accept_new_approved_status
     * @throws IllegalStateTransitionException
     * @throws \Exception
     * @return void
     */
    public function approved_transaction_should_not_accept_new_declined_status(ChargeTransaction $transaction): void
    {
        $this->expectException(IllegalStateTransitionException::class);
        $transaction->decline();
    }

    /**
     * @test
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     */
    public function it_should_return_null_response_interaction_for_three_ds_two_payload_if_no_biller_interaction_was_done(): ChargeTransaction
    {
        $transaction = $this->createPendingTransactionWithRebillForNewCreditCard();

        $this->assertNull($transaction->responsePayloadThreeDsTwo());

        return $transaction;
    }

    /**
     * @test
     * @depends it_should_return_null_response_interaction_for_three_ds_two_payload_if_no_biller_interaction_was_done
     * @param ChargeTransaction $transaction Charge transaction
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidThreedsVersionException
     */
    public function it_should_return_latest_response_interaction_for_three_ds_two_payload(ChargeTransaction $transaction): void
    {
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reasonCode'   => '202',
                            'responseCode' => '2'
                        ],
                        'reason'   => '202',
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            ),
        );

        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ['request' => 'json'],
                        'response' => [
                            'reasonCode'   => '225',
                            'responseCode' => '2'
                        ],
                        'reason'   => '225',
                        'code'     => '2',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            ),
        );

        $this->assertSame(
            [
                'reasonCode'   => '225',
                'responseCode' => '2'
            ],
            json_decode($transaction->responsePayloadThreeDsTwo(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @test
     * @return ChargeTransaction
     * @throws \Exception
     */
    public function create_with_existing_credit_card_info_should_return_a_transaction(): ChargeTransaction
    {
        $transaction = $this->createPendingTransactionWithRebillForExistingCreditCard();

        $this->assertInstanceOf(ChargeTransaction::class, $transaction);

        return $transaction;
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_have_an_id(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(TransactionId::class, $transaction->transactionId());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_have_a_biller_name(ChargeTransaction $transaction): void
    {
        $this->assertIsString($transaction->billerName());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_have_payment_information(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(PaymentTemplateInformation::class, $transaction->paymentInformation());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_have_charge_information(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(ChargeInformation::class, $transaction->chargeInformation());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_have_biller_charge_settings(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(RocketGateBillerSettings::class, $transaction->billerChargeSettings());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_have_a_created_at_date(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $transaction->createdAt());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_have_an_update_at_date(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $transaction->updatedAt());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return ChargeTransaction
     */
    public function created_existing_credit_card_transaction_should_have_a_status(ChargeTransaction $transaction): ChargeTransaction
    {
        $this->assertInstanceOf(Status::class, $transaction->status());

        return $transaction;
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_have_a_pending_status(ChargeTransaction $transaction): void
    {
        $this->assertEquals('pending', (string) $transaction->status());
    }

    /**
     * @test
     * @param ChargeTransaction $transaction The Transaction object
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_not_have_biller_interactions(ChargeTransaction $transaction): void
    {
        $this->assertEquals(0, $transaction->billerInteractions()->count());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function auth_required_on_existing_credit_card_info_should_return_not_update_3ds_version()
    {
        $transaction = $this->createPendingTransactionWithRebillForExistingCreditCard();

        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => ["request" => "json"],
                        'response' => [
                            'reason_code'   => '2',
                            'response_code' => '202',
                            'reason_desc'   => 'Success'
                        ],
                        'reason'   => '2',
                        'code'     => '202',
                    ],
                    JSON_THROW_ON_ERROR
                ),
                new \DateTimeImmutable()
            )
        );
        $this->assertNull($transaction->threedsVersion());
    }

    /**
     * @test
     * @return ChargeTransaction
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function it_should_create_an_epoch_charge_transaction_with_an_other_payment_type(): ChargeTransaction
    {
        $command = $this->createPerformEpochNewSaleCommand();

        $transaction = ChargeTransaction::createSingleChargeOnEpoch(
            $command->siteId(),
            $command->siteName(),
            EpochBillerSettings::EPOCH,
            $command->amount(),
            $command->currency(),
            'ewallet',
            'paypal',
            $command->billerFields(),
            $command->member() ? $command->member()->userName() : null,
            $command->member() ? $command->member()->password() : null
        );

        $this->assertInstanceOf(ChargeTransaction::class, $transaction);

        return $transaction;
    }

    /**
     * @test
     * @depends it_should_create_an_epoch_charge_transaction_with_an_other_payment_type
     * @param ChargeTransaction $transaction Charge transaction
     * @return void
     */
    public function it_should_contain_the_correct_payment_type_for_epoch_with_other_type(
        ChargeTransaction $transaction
    ): void {
        $this->assertSame('ewallet', $transaction->paymentType());
    }

    /**
     * @test
     * @depends it_should_create_an_epoch_charge_transaction_with_an_other_payment_type
     * @param ChargeTransaction $transaction Charge transaction
     * @return void
     */
    public function it_should_contain_the_correct_payment_method_for_epoch_with_other_type(
        ChargeTransaction $transaction
    ): void {
        $this->assertSame('paypal', $transaction->paymentMethod());
    }
}
