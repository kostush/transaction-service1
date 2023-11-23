<?php
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services\Legacy;

use ProbillerNG\LegacyServiceClient\ApiException;
use ProbillerNG\LegacyServiceClient\Model\GeneratePurchaseUrlResponse;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MainPurchaseNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\NotAllowedMoreThanOneMainPurchaseException;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\LegacyServiceResponseException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyGeneratePurchaseUrlAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyNewSaleClient;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Legacy\LegacyNewSaleTranslator;
use Tests\UnitTestCase;

/**
 * @group legacyService
 * Class LegacyNewSaleAdapterTest
 * @package Tests\Unit\Infrastructure\Domain\Services\Legacy
 */
class LegacyNewSaleAdapterTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws ApiException
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MainPurchaseNotFoundException
     * @throws MissingChargeInformationException
     * @throws NotAllowedMoreThanOneMainPurchaseException
     * @throws LegacyServiceResponseException
     */
    public function legacy_adapter_should_return_new_sale_biller_response(): void
    {
        $mockedClientResponse = $this->createMock(GeneratePurchaseUrlResponse::class);
        $mockedClientResponse->method('getRedirectUrl')->willReturn($this->faker->url);

        $legacyClient = $this->createMock(LegacyNewSaleClient::class);
        $legacyClient->method('generatePurchaseUrl')->willReturn(
            $mockedClientResponse
        );

        $adapter = new LegacyGeneratePurchaseUrlAdapter($legacyClient, new LegacyNewSaleTranslator());

        $charges = ChargesCollection::createFromArray(
            [
                [
                    'currency'       => 'USD',
                    'productId'      => 1234,
                    'siteId'         => 'siteId',
                    'isMainPurchase' => true
                ]
            ]
        );

        $mockedLegacyBillerFields = $this->createMock(LegacyBillerChargeSettings::class);
        $mockedTransaction        = $this->createMock(ChargeTransaction::class);
        $mockedTransaction->method('billerChargeSettings')->willReturn($mockedLegacyBillerFields);
        $mockedMember = $this->createMock(Member::class);

        $response = $adapter->newSale($mockedTransaction, $charges, $mockedMember);

        $this->assertInstanceOf(LegacyNewSaleBillerResponse::class, $response);
    }
}
