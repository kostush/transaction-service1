<?php
declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\NetbillingCardHash;
use ProBillerNG\Transaction\Domain\Model\NetbillingPaymentTemplateInformation;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillExistingCardTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillNewCardTranslator;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingUpdateRebillTranslatorFactory;
use Tests\CreatesTransactionData;
use Tests\IntegrationTestCase;

class NetbillingUpdateRebillTranslatorFactoryTest extends IntegrationTestCase
{
    use CreatesTransactionData;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @return void
     */
    public function it_should_return_existing_card_translator_if_payment_template_is_provided()
    {
        $updateRebillTranslatorFactory    = new NetbillingUpdateRebillTranslatorFactory();
        $netbillingCardHash               = $this->createMock(NetbillingCardHash::class);
        $paymentTemplate                  = NetbillingPaymentTemplateInformation::create($netbillingCardHash);
        $netbillingUpdateRebillTranslator = $updateRebillTranslatorFactory->createUpdateRebillTranslator($paymentTemplate);
        $this->assertInstanceOf(NetbillingUpdateRebillExistingCardTranslator::class, $netbillingUpdateRebillTranslator);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException
     */
    public function it_should_return_new_card_translator_if_new_card_is_provided()
    {
        $updateRebillTranslatorFactory    = new NetbillingUpdateRebillTranslatorFactory();
        $netbillingUpdateRebillTranslator = $updateRebillTranslatorFactory->createUpdateRebillTranslator($this->createCreditCardInformation());
        $this->assertInstanceOf(NetbillingUpdateRebillNewCardTranslator::class, $netbillingUpdateRebillTranslator);
    }
}
