<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model\Transaction;

use Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\ChargeInformation;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionAbortedEvent;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionApprovedEvent;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionDeclinedEvent;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\PaymentInformation;
use ProBillerNG\Transaction\Domain\Model\Status;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionCreatedEvent;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use Tests\CreateTransactionDataForNetbilling;
use Tests\UnitTestCase;

class NetbillingChargeTransactionTest extends UnitTestCase
{
    use CreateTransactionDataForNetbilling;

    /**
     * @test
     * @return ChargeTransaction
     * @throws Exception
     */
    public function create_should_return_a_transaction(): ChargeTransaction
    {

        $transaction = $this->createNetbillingPendingTransactionWithRebillForNewCreditCard();

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
        $this->assertInstanceOf(NetbillingChargeSettings::class, $transaction->billerChargeSettings());
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
     * @depends create_should_return_a_transaction
     * @param ChargeTransaction $transaction The transaction object
     * @return void
     */
    public function created_transaction_should_have_bin_routing_code(ChargeTransaction $transaction): void
    {
        $this->assertNotEmpty($transaction->billerChargeSettings()->binRouting());
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
     * @depends created_transaction_should_have_a_status
     * @throws IllegalStateTransitionException
     * @throws Exception
     * @return ChargeTransaction
     */
    public function created_transaction_with_pending_status_should_accept_new_approved_status(ChargeTransaction $transaction): ChargeTransaction
    {
        $transaction->updateTransactionFromNetbillingResponse(
            NetbillingBillerResponse::create(
                new \DateTimeImmutable(),
                json_encode(
                    [
                        'request'  => '{"transactionId":"9853e2c2-6bc5-3b1b-9537-80ad433819ad","siteTag":"' . $_ENV['NETBILLING_SITE_TAG_2'] . '","accountId":"' . $_ENV['NETBILLING_ACCOUNT_ID_2'] . '","initialDays":"30","testMode":true,"memberUsername":"willms.kaylie","memberPassword":"\'~8TX3","amount":"1.00","cardNumber":"************","cardExpire":"0323","cardCvv2":"***","payType":"C","rebillAmount":"0.01","rebillFrequency":"30","firstName":"Amara","lastName":"Larkin","address":"","zipCode":"h2x2l2","city":"","state":"QC","country":"CA","email":"carole92@schinner.com","phone":"","ipAddress":"162.211.96.53","host":"","browser":""}',
                        'response' => '{"avs_code":"X","cvv2_code":"M","status_code":"1","processor":"TEST","auth_code":"999999","settle_amount":"1.00","settle_currency":"USD","trans_id":"114152087528","member_id":"114152087529","auth_msg":"TEST APPROVED","recurring_id":"114152103912","auth_date":"2019-11-26 20:07:58"}',
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
     * @throws Exception
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
        $this->assertInstanceOf(NetbillingChargeSettings::class, $transaction->billerChargeSettings());
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
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
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
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @param ChargeTransaction $transaction The existing card transaction
     * @return void
     */
    public function created_existing_credit_card_transaction_should_have_bin_routing_code(ChargeTransaction $transaction): void
    {
        $this->assertNotEmpty($transaction->billerChargeSettings()->binRouting());
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
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @param ChargeTransaction $transaction Charge Transaction
     * @return void
     */
    public function created_netbilling_transaction_should_have_netbilling_biller_settings(ChargeTransaction $transaction)
    {
        $this->assertInstanceOf(NetbillingChargeSettings::class, $transaction->billerChargeSettings());
    }

    /**
     * @test
     * @depends create_with_existing_credit_card_info_should_return_a_transaction
     * @param ChargeTransaction $transaction Charge Transaction
     * @return void
     */
    public function created_netbilling_existing_card_transaction_should_have_a_netbilling_payment_template(ChargeTransaction $transaction): void
    {
        $this->assertInstanceOf(NetbillingPaymentTemplateInformation::class, $transaction->paymentInformation());
    }
}
