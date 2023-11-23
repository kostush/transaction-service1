<?php
declare(strict_types=1);

namespace Tests\Integration\Infastructure\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\Event\BaseEvent;
use ProBillerNG\Transaction\Domain\Model\Approved;
use ProBillerNG\Transaction\Domain\Model\Event\TransactionCreatedEvent;
use ProBillerNG\Transaction\Domain\Model\NetbillingCardHash;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use Tests\CreateTransactionDataForNetbilling;
use Tests\IntegrationTestCase;

class TransactionCreatedEventWithNetbillingTest extends IntegrationTestCase
{
    use CreateTransactionDataForNetbilling;
    /**
     * @var string
     */
    private $transactionId = '49027f7d-97fe-4270-8f51-d7ba4ff4fc31';

    /**
     * @var string
     */
    private $paymentType = 'cc';

    private $billerSettings = [];

    /**
     * @test
     * @return TransactionCreatedEvent
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException
     * @throws \ProBillerNG\Transaction\Exception
     */
    public function it_should_create_when_recurring_charge_is_informed(): TransactionCreatedEvent
    {
        $this->billerSettings = $this->createNetbillingChargeSettings(null);
        $transactionEvent     = new TransactionCreatedEvent(
            BaseEvent::CHARGE_TRANSACTION,
            $this->transactionId,
            BillerSettings::NETBILLING,
            $this->billerSettings,
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
     * @depends it_should_create_when_recurring_charge_is_informed
     * @param TransactionCreatedEvent $transactionEvent The transaction event
     * @return void
     */
    public function event_should_contain_netbilling_biller_settings(
        TransactionCreatedEvent $transactionEvent
    ): void {
        $this->assertIsArray($transactionEvent->billerSettings());
    }

    /**
     * @test
     * @throws \Exception
     * @return void
     */
    public function it_should_create_a_transaction_created_domain_event_for_a_transaction_with_netbilling_payment_template(): void
    {
        $nbCardHash = $_ENV['NETBILLING_CARD_HASH'];

        $t = new TransactionCreatedEvent(
            BaseEvent::CHARGE_TRANSACTION,
            $this->faker->uuid,
            BillerSettings::NETBILLING,
            $this->createNetbillingBillerFields(),
            (string) Approved::create(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            'cc',
            $this->faker->uuid,
            NetbillingPaymentTemplateInformation::create(
                NetbillingCardHash::create($nbCardHash)
            ),
            $this->createChargeInformationSingleCharge(),
            '0',
            'code',
            null,
            null,
            null
        );

        $this->assertEquals($t->cardHash(), $nbCardHash);
    }
}
