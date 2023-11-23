<?php
declare(strict_types=1);

namespace Tests\Unit\Application\DTO;

use DateTimeImmutable;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\DTO\HttpCommandDTOAssembler;
use ProBillerNG\Transaction\Application\Services\PrepaidInfoExtractorTrait;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidThreedsVersionException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use Tests\Faker;
use Tests\UnitTestCase;

class PrepaidInfoTypeTest extends UnitTestCase
{
    use Faker;
    use PrepaidInfoExtractorTrait;

    /**
     * @param array|null $exclude Exclude Path.
     *
     * @return ChargeTransaction
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     * @throws InvalidThreedsVersionException
     */
    public function getSuccessfulTransaction(array $exclude = []): ChargeTransaction
    {
        $transaction = $this->createPendingTransactionWithRebillForExistingCreditCard();
        $transaction->updateRocketgateTransactionFromBillerResponse(
            RocketgateCreditCardBillerResponse::create(
                new DateTimeImmutable(),
                json_encode($this->createRocketgateBillerResponse($exclude)),
                new DateTimeImmutable()
            )
        );

        return $transaction;
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_containing_balance_info_should_reflect_in_purchase_response(): void
    {
        $transaction   = $this->getSuccessfulTransaction();
        $responseArray = json_decode(json_encode((new HttpCommandDTOAssembler())->assemble($transaction)), true);

        $this->assertArrayHasKey('prepaid', $responseArray);
        $this->assertArrayHasKey('amount', $responseArray['prepaid']);
        $this->assertArrayHasKey('currency', $responseArray['prepaid']);
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_containing_no_balance_amount_should_have_response_without_prepaid_info(): void
    {
        $transaction   = $this->getSuccessfulTransaction(['response.balanceAmount']);
        $responseArray = json_decode(json_encode((new HttpCommandDTOAssembler())->assemble($transaction)), true);

        $this->assertArrayNotHasKey('prepaid', $responseArray);
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_containing_no_balance_currency_should_have_response_without_prepaid_info(): void
    {
        $transaction   = $this->getSuccessfulTransaction(['response.balanceCurrency']);
        $responseArray = json_decode(json_encode((new HttpCommandDTOAssembler())->assemble($transaction)), true);

        $this->assertArrayNotHasKey('prepaid', $responseArray);
    }


    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_containing_no_balance_info_should_have_response_without_prepaid_info(): void
    {
        $transaction   = $this->getSuccessfulTransaction(['response.balanceCurrency', 'response.balanceCurrency']);
        $responseArray = json_decode(json_encode((new HttpCommandDTOAssembler())->assemble($transaction)), true);

        $this->assertArrayNotHasKey('prepaid', $responseArray);
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_containing_balance_amount_info_should_be_retrievable(): void
    {
        $balanceAmount = $this->getAttribute($this->getSuccessfulTransaction(), 'balanceAmount');

        $this->assertNotNull($balanceAmount, 'prepaid balance amount retrieved');
        $this->assertEquals($balanceAmount, '2.36');
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_containing_balance_currency_info_should_be_retrievable(): void
    {
        $balanceCurrency = $this->getAttribute($this->getSuccessfulTransaction(), 'balanceCurrency');

        $this->assertNotNull($balanceCurrency, 'prepaid balance amount retrieved');
        $this->assertEquals($balanceCurrency, 'CAD');
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_not_containing_balance_amount_info_should_not_be_retrievable(): void
    {
        $balanceAmount = $this->getAttribute(
            $this->getSuccessfulTransaction(['response.balanceAmount']),
            'balanceAmount'
        );

        $this->assertNull($balanceAmount, 'prepaid balance amount retrieved');
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_not_containing_balance_currency_info_should_not_be_retrievable(): void
    {
        $balanceCurrency = $this->getAttribute(
            $this->getSuccessfulTransaction(['response.balanceCurrency']),
            'balanceCurrency'
        );

        $this->assertNull($balanceCurrency, 'prepaid balance amount retrieved');
    }


    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_containing_balance_info_should_have_prepaid_type_vo_available(): void
    {
        $prepaidTypeInfoVo = $this->prepaidInfoType($this->getSuccessfulTransaction());

        $this->assertEquals(true, $prepaidTypeInfoVo->isAvailable());
        $this->assertEquals('CAD', $prepaidTypeInfoVo->currency());
        $this->assertEquals('2.36', $prepaidTypeInfoVo->amount());
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_not_containing_balance_currency_should_have_prepaid_type_vo_not_available(): void
    {
        $prepaidTypeInfoVo = $this->prepaidInfoType($this->getSuccessfulTransaction(['response.balanceCurrency']));

        $this->assertEquals(false, $prepaidTypeInfoVo->isAvailable());
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_not_containing_balance_amount_should_have_prepaid_type_vo_not_available(): void
    {
        $prepaidTypeInfoVo = $this->prepaidInfoType($this->getSuccessfulTransaction(['response.balanceAmount']));

        $this->assertEquals(false, $prepaidTypeInfoVo->isAvailable());
    }


    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_without_response_should_not_provide_response_payload(): void
    {
        self::assertNull($this->getSuccessfulTransaction(['response.*'])->responsePayload());
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_should_provide_response_payload_with_balance_info(): void
    {
        $responsePayload = json_decode($this->getSuccessfulTransaction()->responsePayload(), true);

        $this->assertNotNull($responsePayload);
        $this->assertArrayHasKey('balanceCurrency', $responsePayload);
        $this->assertArrayHasKey('balanceAmount', $responsePayload);
    }


    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_should_provide_response_payload_with_balance_currency(): void
    {
        $responsePayload = json_decode($this->getSuccessfulTransaction()->responsePayload(), true);

        $this->assertNotNull($responsePayload);
        $this->assertArrayHasKey('balanceCurrency', $responsePayload);
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_should_provide_response_payload_with_balance_amount(): void
    {
        $responsePayload = json_decode($this->getSuccessfulTransaction()->responsePayload(), true);

        $this->assertNotNull($responsePayload);
        $this->assertArrayHasKey('balanceAmount', $responsePayload);
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_without_balance_amount_should_provide_response_payload_without_balance_amount(): void
    {
        $responsePayload = json_decode(
            $this->getSuccessfulTransaction(['response.balanceAmount'])->responsePayload(),
            true
        );

        $this->assertNotNull($responsePayload);
        $this->assertArrayNotHasKey('balanceAmount', $responsePayload);
    }

    /**
     * @group        PrepaidInfo
     * @test
     *
     * @return void
     * @throws Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidThreedsVersionException
     * @throws MissingChargeInformationException
     * @throws MissingCreditCardInformationException
     */
    public function transaction_with_transaction_biller_response_without_balance_currency_should_provide_response_payload_without_balance_currency(): void
    {
        $responsePayload = json_decode(
            $this->getSuccessfulTransaction(['response.balanceCurrency'])->responsePayload(),
            true
        );

        $this->assertNotNull($responsePayload);
        $this->assertArrayNotHasKey('balanceCurrency', $responsePayload);
    }
}
