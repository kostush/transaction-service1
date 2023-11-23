<?php

declare(strict_types=1);

namespace Tests\Unit\Infastructure\Domain\Services;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidThreedsVersionException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\UnknownPaymentMethodForBillerException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\UnknownPaymentTypeForBillerException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateChargeService;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCreditCardTranslationService;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateOtherPaymentTypeChargeAdapter;
use Tests\UnitTestCase;

class RocketgateChargeServiceTest extends UnitTestCase
{
    /**
     * @var MockObject
     */
    private $rocketgateService;

    /**
     * @var RocketgateChargeService
     */
    private $service;

    /**
     * Setup before test
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rocketgateService = $this->createMock(RocketgateCreditCardTranslationService::class);

        $this->service = new RocketgateChargeService(
            $this->rocketgateService,
            $this->createMock(RocketgateOtherPaymentTypeChargeAdapter::class)
        );
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     * @throws UnknownPaymentTypeForBillerException
     */
    public function charge_with_new_credit_card_should_throw_an_unhandled_payment_method_for_biller_exception_when_payment_method_unhandled()
    {
        $this->expectException(InvalidPaymentTypeException::class);

        $this->service->chargeNewCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard(['type' => 'invalidPaymentMethod'])
        );
    }

    /**
     * @test
     * @depends charge_with_new_credit_card_should_throw_an_unhandled_payment_method_for_biller_exception_when_payment_method_unhandled
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     * @throws UnknownPaymentTypeForBillerException
     */
    public function charge_with_new_credit_card_should_call_rocketgate_credit_card_service_when_rocketgate_biller_id_given(): void
    {
        $this->rocketgateService->expects($this->once())->method('chargeWithNewCreditCard');

        $this->service->chargeNewCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard()
        );
    }


    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws UnknownPaymentTypeForBillerException
     */
    public function charge_with_existing_credit_card_should_throw_an_unhandled_payment_method_for_biller_exception_when_payment_method_unhandled(): void
    {
        $this->expectException(InvalidPaymentTypeException::class);

        $this->service->chargeExistingCreditCard(
            $this->createPendingTransactionWithRebillForExistingCreditCard(['type' => 'invalidPaymentMethod'])
        );
    }

    /**
     * @test
     * @depends charge_with_existing_credit_card_should_throw_an_unhandled_payment_method_for_biller_exception_when_payment_method_unhandled
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws UnknownPaymentTypeForBillerException
     */
    public function charge_with_existing_credit_card_should_call_rocketgate_credit_card_service_when_rocketgate_biller_id_given(): void
    {
        $this->rocketgateService->expects($this->once())->method('chargeWithExistingCreditCard');

        $this->service->chargeExistingCreditCard(
            $this->createPendingTransactionWithRebillForExistingCreditCard()
        );
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidMerchantInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidThreedsVersionException
     */
    public function suspend_rebill_should_call_rocketgate_credit_card_service(): void
    {
        $this->rocketgateService->expects($this->once())->method('suspendRebill');

        $this->service->suspendRebill(
            $this->createCancelRebillRocketgateTransaction()
        );
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     */
    public function complete_threeD_credit_card_should_call_rocketgate_credit_card_service(): void
    {
        $this->rocketgateService->expects($this->once())->method('completeThreeDCreditCard');

        $this->service->completeThreeDCreditCard(
            $this->createPendingTransactionWithRebillForNewCreditCard(
                [
                    'useThreeD' => true
                ]
            ),
            'SimulatedPARES10001000E00B000',
            null
        );
    }

    /**
     * @test
     * @throws Exception
     * @throws UnknownPaymentMethodForBillerException
     * @return void
     */
    public function it_should_throw_exception_if_charge_other_payment_type_method_different_than_check():void
    {
        $mockedTransaction = $this->createMock(ChargeTransaction::class);
        $mockedTransaction->method('paymentMethod')->willReturn('debit');

        $this->expectException(UnknownPaymentMethodForBillerException::class);
        $this->service->chargeOtherPaymentType($mockedTransaction);
    }
}
