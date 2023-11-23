<?php
declare(strict_types=1);

namespace Tests\Unit\Application\DTO\ReturnTypes;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RebillUpdateTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use Tests\UnitTestCase;

class RebillUpdateTransactionReturnTypeTest extends UnitTestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Have rebill and amount on charge information.
     *
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws LoggerException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_return_a_rebill_update_transaction_return_type_when_having_charge_information()
    {
        $transaction = $this->createUpdateRebillTransaction();

        $transactionReturnType = RebillUpdateTransactionReturnType::createFromTransaction($transaction);

        $this->assertInstanceOf(RebillUpdateTransactionReturnType::class, $transactionReturnType);
    }

    /**
     * Have no rebill, but have amount on charge information.
     *
     * @test
     * @return void
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws LoggerException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_return_a_rebill_update_transaction_return_type_when_missing_rebill_charge_information()
    {
        $transaction = $this->createPendingRocketgateTransactionSingleCharge();

        $transactionReturnType = RebillUpdateTransactionReturnType::createFromTransaction($transaction);

        $this->assertInstanceOf(RebillUpdateTransactionReturnType::class, $transactionReturnType);
    }

    /**
     * Have no charge information at all.
     *
     * @test
     * @return void
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws LoggerException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws MissingMerchantInformationException
     */
    public function it_should_return_a_rebill_update_transaction_return_type_when_missing_charge_information()
    {
        $transaction = $this->createCancelRebillRocketgateTransaction();

        $transactionReturnType = RebillUpdateTransactionReturnType::createFromTransaction($transaction);

        $this->assertInstanceOf(RebillUpdateTransactionReturnType::class, $transactionReturnType);
    }
}
