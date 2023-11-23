<?php
declare(strict_types=1);

namespace Tests\Integration\Domain\Model\Event;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\CheckInformation;
use ProBillerNG\Transaction\Domain\Model\Event\BaseEvent;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionCreatedEvent;
use ProBillerNG\Transaction\Domain\Model\Exception\BillerSettingObfuscatorNotDefined;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\ObfuscatedData;
use ProBillerNG\Transaction\Domain\Model\PaymentTemplateInformation;
use ProBillerNG\Transaction\Domain\Model\RocketGateCardHash;
use Tests\UnitTestCase;

class TransactionCreatedEventTest extends UnitTestCase
{
    /**
     * @var string
     */
    private $transactionId = '49027f7d-97fe-4270-8f51-d7ba4ff4fc34';

    /**
     * @var string
     */
    private $paymentType = 'cc';

    /**
     * @test
     * @return TransactionCreatedEvent
     * @throws Exception
     * @throws BillerSettingObfuscatorNotDefined
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_create_a_transaction_created_domain_event_for_a_recurring_charge_transaction(): TransactionCreatedEvent
    {
        $transactionEvent = new TransactionCreatedEvent(
            BaseEvent::CHARGE_TRANSACTION,
            $this->transactionId,
            BillerSettings::ROCKETGATE,
            $this->createRocketgateChargeSettings(),
            (string) Approved::create(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            $this->paymentType,
            $this->faker->uuid,
            $this->createCreditCardInformation(),
            $this->createChargeInformationWithRebill(),
            '0',
            'code',
            null,
            null,
            null
        );

        $this->assertInstanceOf(TransactionCreatedEvent::class, $transactionEvent);

        return $transactionEvent;
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_domain_event_for_a_recurring_charge_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function it_should_contain_biller_settings(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertIsArray($transactionEvent->billerSettings());
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_domain_event_for_a_recurring_charge_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function it_should_have_correct_version_property(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertSame($transactionEvent->version(), BaseEvent::LATEST_VERSION);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_domain_event_for_a_recurring_charge_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function it_should_have_correct_transaction_type(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertSame($transactionEvent->transactionType(), BaseEvent::CHARGE_TRANSACTION);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_domain_event_for_a_recurring_charge_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function it_should_have_correct_payment_type(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertSame($transactionEvent->paymentType(), $this->paymentType);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_domain_event_for_a_recurring_charge_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function it_should_have_correct_transaction_id(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertSame($transactionEvent->transactionId(), $this->transactionId);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_domain_event_for_a_recurring_charge_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function it_should_have_correct_previous_transaction_id(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertNull($transactionEvent->previousTransactionId());
    }

    /**
     * @param TransactionCreatedEvent $transactionEvent Transaction Event
     * @test
     * @depends it_should_create_a_transaction_created_domain_event_for_a_recurring_charge_transaction
     * @return void
     */
    public function it_should_contain_an_obfuscated_merchant_password(TransactionCreatedEvent $transactionEvent): void
    {
        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $transactionEvent->billerSettings()['merchantPassword']);
    }

    /**
     * @param TransactionCreatedEvent $transactionEvent Transaction Event
     * @test
     * @depends it_should_create_a_transaction_created_domain_event_for_a_recurring_charge_transaction
     * @return void
     */
    public function it_should_contain_credit_card_information_when_it_is_new_cc(TransactionCreatedEvent $transactionEvent): void
    {
        $this->assertNotEmpty($transactionEvent->cvv2Check());
        $this->assertNotEmpty($transactionEvent->firstSix());
        $this->assertNotEmpty($transactionEvent->lastFour());
        $this->assertNotEmpty($transactionEvent->expirationYear());
        $this->assertNotEmpty($transactionEvent->expirationMonth());
        $this->assertNotEmpty($transactionEvent->creditCardNumber());
    }

    /**
     * @test
     * @return void
     * @throws BillerSettingObfuscatorNotDefined
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_not_contain_credit_card_information_when_payment_information_is_from_checks(): void
    {
        $transactionEvent = new TransactionCreatedEvent(
            BaseEvent::CHARGE_TRANSACTION,
            $this->transactionId,
            BillerSettings::ROCKETGATE,
            $this->createRocketgateChargeSettings(),
            (string) Approved::create(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            $this->paymentType,
            $this->faker->uuid,
            $this->createCheckInformation(),
            $this->createChargeInformationWithRebill(),
            '0',
            'code',
            null,
            null,
            null
        );

        $this->assertEmpty($transactionEvent->cvv2Check());
        $this->assertEmpty($transactionEvent->firstSix());
        $this->assertEmpty($transactionEvent->lastFour());
        $this->assertEmpty($transactionEvent->expirationYear());
        $this->assertEmpty($transactionEvent->expirationMonth());
        $this->assertEmpty($transactionEvent->creditCardNumber());
    }

    /**
     * @test
     * @throws Exception
     * @throws BillerSettingObfuscatorNotDefined
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_create_a_transaction_created_domain_event_for_a_non_recurring_charge_transaction(): void
    {
        $t = new TransactionCreatedEvent(
            BaseEvent::CHARGE_TRANSACTION,
            $this->faker->uuid,
            BillerSettings::ROCKETGATE,
            $this->createRocketgateChargeSettings(),
            (string) Approved::create(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            'cc',
            $this->faker->uuid,
            $this->createCreditCardInformation(),
            $this->createChargeInformationSingleCharge(),
            '0',
            'code',
            null,
            null,
            null
        );

        $this->assertInstanceOf(TransactionCreatedEvent::class, $t);
    }

    /**
     * @test
     * @throws Exception
     * @throws BillerSettingObfuscatorNotDefined
     * @throws InvalidChargeInformationException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_create_a_transaction_created_domain_event_for_a_transaction_with_payment_template(): void
    {
        $t = new TransactionCreatedEvent(
            BaseEvent::CHARGE_TRANSACTION,
            $this->faker->uuid,
            BillerSettings::ROCKETGATE,
            $this->createRocketgateChargeSettings(),
            (string) Approved::create(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            'cc',
            $this->faker->uuid,
            PaymentTemplateInformation::create(
                RocketGateCardHash::create($_ENV['ROCKETGATE_CARD_HASH_1'])
            ),
            $this->createChargeInformationSingleCharge(),
            '0',
            'code',
            null,
            null,
            null
        );

        $this->assertInstanceOf(TransactionCreatedEvent::class, $t);
    }


    /**
     * @test
     * @return TransactionCreatedEvent
     * @throws Exception
     * @throws BillerSettingObfuscatorNotDefined
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_create_a_transaction_created_event_for_a_rebill_update_transaction(): TransactionCreatedEvent
    {
        $transaction = new TransactionCreatedEvent(
            BaseEvent::REBILL_UPDATE_TRANSACTION,
            $this->transactionId,
            BillerSettings::ROCKETGATE,
            $this->createRocketgateChargeSettings(),
            (string) Approved::create(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            null,
            null,
            null,
            null,
            '0',
            'code',
            null,
            null,
            null
        );

        $this->assertInstanceOf(TransactionCreatedEvent::class, $transaction);

        return $transaction;
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_event_for_a_rebill_update_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function rebill_update_transaction_created_event_should_have_correct_version_property(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertSame($transactionEvent->version(), BaseEvent::LATEST_VERSION);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_event_for_a_rebill_update_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function rebill_update_transaction_created_event_should_have_correct_transaction_type(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertSame($transactionEvent->transactionType(), BaseEvent::REBILL_UPDATE_TRANSACTION);
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_event_for_a_rebill_update_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function rebill_update_transaction_created_event_should_have_correct_payment_type(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertNull($transactionEvent->paymentType());
    }

    /**
     * @test
     * @depends it_should_create_a_transaction_created_event_for_a_rebill_update_transaction
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function rebill_update_transaction_created_event_should_have_correct_transaction_id(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertSame($transactionEvent->transactionId(), $this->transactionId);
    }

    /**
     * @test
     * @throws Exception
     * @throws BillerSettingObfuscatorNotDefined
     * @throws InvalidChargeInformationException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     * @return void
     */
    public function it_should_create_with_check_information_and_no_credit_card_data(): void
    {
        $checkInformation = $this->createMock(CheckInformation::class);

        $transactionEvent = new TransactionCreatedEvent(
            BaseEvent::CHARGE_TRANSACTION,
            $this->transactionId,
            BillerSettings::ROCKETGATE,
            $this->createRocketgateChargeSettings(),
            (string) Approved::create(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            $this->paymentType,
            $this->faker->uuid,
            $checkInformation,
            $this->createChargeInformationWithRebill(),
            '0',
            'code',
            null,
            null,
            null
        );

        $this->assertInstanceOf(TransactionCreatedEvent::class, $transactionEvent);
    }
}
