<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Rebill;
use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\Model\Amount;
use ProBillerNG\Transaction\Domain\Model\Exception\AfterTaxDoesNotMatchWithAmountException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\TaxAmount;
use ProBillerNG\Transaction\Domain\Model\TaxInformation;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class TaxInformationTest
 * @package Tests\Unit\Domain\Model
 */
class TaxInformationTest extends UnitTestCase
{

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     */
    public function it_should_give_a_specific_exception_message_to_validate_initial_amount(): void
    {
        $messageFormat = Code::getMessage(Code::AFTER_TAX_DOES_NOT_MATCH_WITH_AMOUNT);
        $message       = sprintf($messageFormat, TaxAmount::INTIAL_TAX_AMOUNT, TaxAmount::AMOUNT);

        $this->expectException(AfterTaxDoesNotMatchWithAmountException::class);
        $this->expectExceptionMessage($message);

        $initialAfterTax = '10';
        $rebillAfterTax  = '11';

        $tax            = $this->returnTaxArray($initialAfterTax, $rebillAfterTax);
        $taxInformation = TaxInformation::createFromArray($tax);

        $taxInformation->validateAfterTaxAmount(Amount::create(10.99), null);
    }

    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws AfterTaxDoesNotMatchWithAmountException
     */
    public function it_should_give_a_specific_exception_message_to_validate_rebill_amount(): void
    {
        $messageFormat = Code::getMessage(Code::AFTER_TAX_DOES_NOT_MATCH_WITH_AMOUNT);
        $message       = sprintf($messageFormat, TaxAmount::REBILL_TAX_AMOUNT, TaxAmount::REBILL_AMOUNT);

        $this->expectException(AfterTaxDoesNotMatchWithAmountException::class);
        $this->expectExceptionMessage($message);

        $initialAfterTax = '10';
        $rebillAfterTax  = '11';
        $rebill          = Rebill::create(1, 1, Amount::create(11.1));

        $tax            = $this->returnTaxArray($initialAfterTax, $rebillAfterTax);
        $taxInformation = TaxInformation::createFromArray($tax);

        $taxInformation->validateAfterTaxAmount(null, $rebill);
    }

    /**
     * @param string $initialAfterTax Initial After tax
     * @param string $rebillAfterTax  Rebill tax
     * @return array
     */
    private function returnTaxArray(string $initialAfterTax, string $rebillAfterTax): array
    {
        return [
            "initialAmount"        => [
                "beforeTaxes" => 1,
                "taxes"       => 0.5,
                "afterTaxes"  => $initialAfterTax
            ],
            "rebillAmount"        => [
                "beforeTaxes" => 1,
                "taxes"       => 0.5,
                "afterTaxes"  => $rebillAfterTax
            ],
            "taxApplicationId"     => "60bf5bcb-ac64-496c-acc5-9c7cf54a1869",
            "taxName"              => "VAT",
            "taxType"              => "vat",
            "taxRate"              => 0.05,
            "displayChargedAmount" => $this->faker->boolean
        ];
    }
}
