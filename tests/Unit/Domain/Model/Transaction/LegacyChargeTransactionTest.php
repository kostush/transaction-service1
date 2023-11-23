<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model\Transaction;

use PHPUnit\Framework\MockObject\MockObject;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Amount;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\AfterTaxDoesNotMatchWithAmountException;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MainPurchaseNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\NotAllowedMoreThanOneMainPurchaseException;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyPostbackBillerResponse;
use Tests\CreatesTransactionData;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class LegacyChargeTransactionTest
 * @package Tests\Unit\Domain\Model\Transaction
 */
class LegacyChargeTransactionTest extends UnitTestCase
{
    use CreatesTransactionData;

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws MissingChargeInformationException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     * @throws IllegalStateTransitionException
     */
    public function it_should_update_amount_and_rebill_fields_when_response_is_approved(): void
    {
        $amount       = $this->faker->randomFloat();
        $rebillAmount = $this->faker->randomFloat();
        $mockedRebill = $this->createMock(Rebill::class);
        $mockedRebill->method('amount')->willReturn(Amount::create($rebillAmount));
        $mockedRebill->method('start')->willReturn($this->faker->randomNumber());
        $mockedRebill->method('frequency')->willReturn($this->faker->randomNumber());

        $transaction = $this->createPendingLegacyTransaction();

        /** @var $mockedLegacyResponse MockObject|LegacyPostbackBillerResponse  **/
        $mockedLegacyResponse = $this->createMock(LegacyPostbackBillerResponse::class);
        $mockedLegacyResponse->method('approved')->willReturn(true);
        $mockedLegacyResponse->method('declined')->willReturn(false);
        $mockedLegacyResponse->method('amount')->willReturn(Amount::create($amount));
        $mockedLegacyResponse->method('rebill')->willReturn($mockedRebill);

        $transaction->updateLegacyTransactionFromBillerResponse($mockedLegacyResponse);

        $this->assertEquals($amount, $transaction->chargeInformation()->amount()->value());
        $this->assertEquals($rebillAmount, $transaction->chargeInformation()->rebill()->amount()->value());
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws MissingChargeInformationException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     * @throws IllegalStateTransitionException
     */
    public function it_should_keep_the_amount_and_rebill_fields_when_response_is_approved_and_they_are_null(): void
    {
        $transaction  = $this->createPendingLegacyTransaction();
        $amount       = $transaction->chargeInformation()->amount()->value();
        $rebillAmount = $transaction->chargeInformation()->rebill()->amount()->value();

        /** @var $mockedLegacyResponse MockObject|LegacyPostbackBillerResponse  * */
        $mockedLegacyResponse = $this->createMock(LegacyPostbackBillerResponse::class);
        $mockedLegacyResponse->method('approved')->willReturn(true);
        $mockedLegacyResponse->method('declined')->willReturn(false);
        $mockedLegacyResponse->method('amount')->willReturn(null);
        $mockedLegacyResponse->method('rebill')->willReturn(null);

        $transaction->updateLegacyTransactionFromBillerResponse($mockedLegacyResponse);

        $this->assertEquals($amount, $transaction->chargeInformation()->amount()->value());
        $this->assertEquals($rebillAmount, $transaction->chargeInformation()->rebill()->amount()->value());
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws MissingChargeInformationException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     * @throws AfterTaxDoesNotMatchWithAmountException
     */
    public function it_should_create_legacy_transaction_with_no_member_information(): void
    {
        $transaction = $this->createPendingLegacyTransactionWithNoMember();

        /** @var $mockedLegacyResponse MockObject|LegacyPostbackBillerResponse  * */
        $mockedLegacyResponse = $this->createMock(LegacyPostbackBillerResponse::class);
        $mockedLegacyResponse->method('approved')->willReturn(true);
        $mockedLegacyResponse->method('declined')->willReturn(false);
        $mockedLegacyResponse->method('amount')->willReturn(Amount::create(10.0));
        $mockedLegacyResponse->method('rebill')->willReturn(
            Rebill::create(10, 10, Amount::create(10.0))
        );

        $transaction = ChargeTransaction::createLegacyCrossSaleTransaction(
            $transaction,
            $mockedLegacyResponse,
            $this->faker->uuid
        );

        $this->assertInstanceOf(ChargeTransaction::class, $transaction);
    }

    /**
     * @test
     * @throws AfterTaxDoesNotMatchWithAmountException
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws MissingChargeInformationException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     * @return void
     */
    public function it_should_add_transaction_id_and_legacy_product_id_custom_fields_with_a_transaction_created(): void
    {
        $mainProductId = 123456;
        $transaction   = $this->createPendingLegacyTransactionWithNoMember();

        /** @var LegacyBillerChargeSettings $billerSetting */
        $billerSettingWithNotCustomFields = $transaction->billerChargeSettings();
        $this->assertEmpty($billerSettingWithNotCustomFields->others());

        $transaction->addCustomFieldsToLegacyBillerSetting($mainProductId);

        /** @var LegacyBillerChargeSettings $billerSetting */
        $billerSettingWithCustomFields = $transaction->billerChargeSettings();

        $this->assertEquals(
            (string) $transaction->transactionId(),
            $billerSettingWithCustomFields->others()['custom']['transactionId']
        );
        $this->assertEquals($mainProductId, $billerSettingWithCustomFields->others()['custom']['mainProductId']);
    }
}
