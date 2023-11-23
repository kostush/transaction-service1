<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaRocketgate;
use Tests\UnitTestCase;

/**
 * Class MappingCriteriaRocketgateTest
 * @package Tests\Unit\Domain\Model
 */
class MappingCriteriaRocketgateTest extends UnitTestCase
{
    /**
     * @test
     */
    public function it_should_accept_empty_merchantAccount_and_bankResponseCode_when_create_mapping_criteria()
    {
        $billerResponse = $this->createMock(BillerResponse::class);

        $billerResponse->method('requestPayload')->willReturn(
            json_encode(['merchantID' => '123456789'])
        );

        $billerResponse->method('responsePayload')->willReturn(
            json_encode([
                'merchantAccount'  => '',
                'bankResponseCode' => '',
                'reasonCode'       => '104',
            ])
        );

        $mappingCriteria = MappingCriteriaRocketgate::create($billerResponse);

        $this->assertEmpty($mappingCriteria->merchantAccount());
        $this->assertEmpty($mappingCriteria->bankResponseCode());
        $this->assertEquals(BillerSettings::ROCKETGATE, $mappingCriteria->billerName());
    }

    /**
     * @test
     */
    public function it_should_accept_missing_merchantAccount_and_bankResponseCode_when_create_mapping_criteria()
    {
        $billerResponse = $this->createMock(BillerResponse::class);

        $billerResponse->method('requestPayload')->willReturn(
            json_encode(['merchantID' => '123456789'])
        );

        $billerResponse->method('responsePayload')->willReturn(
            json_encode([
                'reasonCode' => '104',
            ])
        );

        $mappingCriteria = MappingCriteriaRocketgate::create($billerResponse);

        $this->assertEmpty($mappingCriteria->merchantAccount());
        $this->assertEmpty($mappingCriteria->bankResponseCode());
    }

    /**
     * @test
     */
    public function it_should_accept_null_merchantAccount_and_bankResponseCode_when_create_mapping_criteria()
    {
        $billerResponse = $this->createMock(BillerResponse::class);

        $billerResponse->method('requestPayload')->willReturn(
            json_encode(['merchantID' => '123456789'])
        );

        $billerResponse->method('responsePayload')->willReturn(
            json_encode([
                'merchantAccount'  => null,
                'bankResponseCode' => null,
                'reasonCode'       => '104',
            ])
        );

        $mappingCriteria = MappingCriteriaRocketgate::create($billerResponse);

        $this->assertEmpty($mappingCriteria->merchantAccount());
        $this->assertEmpty($mappingCriteria->bankResponseCode());
        $this->assertEquals(BillerSettings::ROCKETGATE, $mappingCriteria->billerName());
    }

    /**
     * @test
     */
    public function it_should_convert_to_string_all_values_received_from_biller_when_create_mapping_criteria()
    {
        $merchantAccount  = 21;
        $bankResponseCode = 121;
        $reasonCode       = 104;

        $billerResponse = $this->createMock(BillerResponse::class);

        $billerResponse->method('requestPayload')->willReturn(
            json_encode(['merchantID' => 125])
        );

        $billerResponse->method('responsePayload')->willReturn(
            json_encode([
                'merchantAccount'  => $merchantAccount,
                'bankResponseCode' => $bankResponseCode,
                'reasonCode'       => $reasonCode,
            ])
        );

        $mappingCriteria = MappingCriteriaRocketgate::create($billerResponse);

        $this->assertIsInt($merchantAccount);
        $this->assertIsString($mappingCriteria->merchantAccount());

        $this->assertIsInt($bankResponseCode);
        $this->assertIsString($mappingCriteria->bankResponseCode());

        $this->assertIsInt($reasonCode);
        $this->assertIsString($mappingCriteria->reasonCode());
    }

    /**
     * @test
     */
    public function it_should_use_empty_string_when_biller_response_is_json_but_missing_required_fields_to_create_mapping_criteria()
    {
        $billerResponse = $this->createMock(BillerResponse::class);

        $billerResponse->method('requestPayload')->willReturn(
            json_encode(['randomRequestField' => 125])
        );

        $billerResponse->method('responsePayload')->willReturn(
            json_encode([
                'randomResponseFieldOne'   => 'test'
            ])
        );

        $mappingCriteria = MappingCriteriaRocketgate::create($billerResponse);

        $this->assertEmpty($mappingCriteria->merchantAccount());
        $this->assertEmpty($mappingCriteria->bankResponseCode());
        $this->assertEmpty($mappingCriteria->reasonCode());
    }
}
