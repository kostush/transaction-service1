<?php
declare(strict_types=1);

namespace Tests\Integration\Infastructure\Domain\Services;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\Services\Transaction\BillerLoginInfo;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Exception as TransactionException;
use ProBillerNG\Transaction\Application\Services\Transaction\ExistingCreditCardInformation;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\BaseNetbillingCancelRebillAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingCreditCardTranslationService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingExistingCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingNewCreditCardChargeAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingCancelRebillAdapter;
use Tests\CreateTransactionDataForNetbilling;
use Tests\IntegrationTestCase;

class NetbillingCreditCardTranslationServiceTest extends IntegrationTestCase
{
    use CreateTransactionDataForNetbilling;

    /**
     * @var NetbillingNewCreditCardChargeAdapter
     */
    private $newCardAdapter;

    /**
     * @var NetbillingExistingCreditCardChargeAdapter
     */
    private $existingCardAdapter;

    /**
     * @var BaseNetbillingCancelRebillAdapter
     */
    private $cancelRebillAdapter;

    /**
     * @var ChargeTransaction
     */
    private $existingCCTransaction;

    /**
     * @return void
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws LoggerException
     * @throws InvalidPayloadException
     * @throws InvalidPaymentInformationException
     * @throws MissingInitialDaysException
     * @throws TransactionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->newCardAdapter      = $this->createMock(NetbillingNewCreditCardChargeAdapter::class);
        $this->existingCardAdapter = $this->createMock(NetbillingExistingCreditCardChargeAdapter::class);
        $this->cancelRebillAdapter = $this->createMock(NetbillingCancelRebillAdapter::class);

        $this->existingCCTransaction = $this->createPendingTransactionWithRebillForExistingCreditCard();
    }

    /**
     * @test
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws LoggerException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     * @throws \ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidPaymentInformationException
     * @return void
     */
    public function charge_with_existing_card_should_call_adapter_charge(): void
    {
        $this->existingCardAdapter->expects($this->once())->method('charge');
        $translation = new NetbillingCreditCardTranslationService(
            $this->newCardAdapter,
            $this->existingCardAdapter,
            $this->cancelRebillAdapter
        );
        $translation->chargeWithExistingCreditCard(
            ChargeTransaction::createSingleChargeOnNetbilling(
                $this->faker->uuid,
                1.00,
                NetbillingBillerSettings::NETBILLING,
                'USD',
                new Payment('cc', new ExistingCreditCardInformation($_ENV['NETBILLING_CARD_HASH'])),
                new NetbillingChargeSettings(
                    'slkfdj',
                    'sldkf',
                    $this->faker->password,
                    30,
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ),
                new BillerLoginInfo('netbillingUser', 'netbillingPassword')
            )
        );
    }
}
