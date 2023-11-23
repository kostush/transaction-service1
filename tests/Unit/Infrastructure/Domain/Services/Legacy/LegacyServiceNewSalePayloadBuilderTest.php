<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services\Legacy;

use ProbillerNG\LegacyServiceClient\Model\GeneratePurchaseUrlRequest;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MainPurchaseNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\NotAllowedMoreThanOneMainPurchaseException;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyServiceNewSalePayloadBuilder;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class LegacyServiceNewSalePayloadBuilderTest
 * @package Tests\Unit\Infrastructure\Domain\Services\Legacy
 */
class LegacyServiceNewSalePayloadBuilderTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws MissingChargeInformationException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     */
    public function it_should_create_legacy_payload_without_tax_rebill_amout(): void
    {

        $mockedTransaction = $this->createMock(ChargeTransaction::class);
        $mockedTransaction->method('isFreeSale')->willReturn(false);
        $mockedLegacyBillerFields = $this->createMock(LegacyBillerChargeSettings::class);
        $mockedTransaction->method('billerChargeSettings')->willReturn($mockedLegacyBillerFields);

        $charges = ChargesCollection::createFromArray(
            [
                [
                    "siteId"         => "8e34c94e-135f-4acb-9141-58b3a6e56c74",
                    "amount"         => 14.97,
                    "currency"       => "USD",
                    "productId"      => 15,
                    "isMainPurchase" => true,
                    "tax"            => [
                        "initialAmount"        => [
                            "beforeTaxes" => 0,
                            "taxes"       => 0,
                            "afterTaxes"  => 14.97
                        ],
                        "taxApplicationId"     => "8e34c94e-135f-4acb-9141-58b3a6e56c74",
                        "taxName"              => "string",
                        "taxRate"              => 0,
                        "taxType"              => "string",
                        "displayChargedAmount" => true
                    ]
                ]
            ]
        );

        $payload = (new LegacyServiceNewSalePayloadBuilder())
            ->createPurchaseUrlPayload($mockedTransaction, $charges, null);

        $this->assertInstanceOf(GeneratePurchaseUrlRequest::class, $payload);
    }
}
