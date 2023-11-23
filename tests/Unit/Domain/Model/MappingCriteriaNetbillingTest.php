<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaNetbilling;
use Tests\UnitTestCase;

/**
 * Class MappingCriteriaNetbillingTest
 * @package Tests\Unit\Domain\Model
 */
class MappingCriteriaNetbillingTest extends UnitTestCase
{
    /**
     * @test
     */
    public function it_should_accept_empty_auth_message_when_create_mapping_criteria()
    {
        $billerResponse = $this->createMock(BillerResponse::class);
        $billerResponse->method('responsePayload')->willReturn(
            json_encode(['processor' => 'Random Processor', 'auth_msg' => ''])
        );

        $mappingCriteria = MappingCriteriaNetbilling::create($billerResponse);

        $this->assertEmpty($mappingCriteria->authMessage());
        $this->assertEquals(BillerSettings::NETBILLING, $mappingCriteria->billerName());
    }

    /**
     * @test
     */
    public function it_should_accept_null_auth_message_when_create_mapping_criteria()
    {
        $billerResponse = $this->createMock(BillerResponse::class);
        $billerResponse->method('responsePayload')->willReturn(
            json_encode(['processor' => 'Random Processor', 'auth_msg' => null])
        );

        $mappingCriteria = MappingCriteriaNetbilling::create($billerResponse);

        $this->assertEmpty($mappingCriteria->authMessage());
        $this->assertEquals(BillerSettings::NETBILLING, $mappingCriteria->billerName());
    }

    /**
     * @test
     */
    public function it_should_accept_missing_auth_message_when_create_mapping_criteria()
    {
        $billerResponse = $this->createMock(BillerResponse::class);
        $billerResponse->method('responsePayload')->willReturn(
            json_encode(['processor' => 'Random Processor'])
        );

        $mappingCriteria = MappingCriteriaNetbilling::create($billerResponse);

        $this->assertEmpty($mappingCriteria->authMessage());
        $this->assertEquals(BillerSettings::NETBILLING, $mappingCriteria->billerName());
    }

    /**
     * @test
     */
    public function it_should_convert_to_string_all_values_received_from_biller_when_create_mapping_criteria()
    {
        $processor   = 1000;
        $authMessage = 9999;

        $billerResponse = $this->createMock(BillerResponse::class);

        $billerResponse->method('responsePayload')->willReturn(
            json_encode([
                'processor' => $processor,
                'auth_msg'  => $authMessage
            ])
        );

        $mappingCriteria = MappingCriteriaNetbilling::create($billerResponse);

        $this->assertIsInt($processor);
        $this->assertIsString($mappingCriteria->processor());

        $this->assertIsInt($authMessage);
        $this->assertIsString($mappingCriteria->authMessage());
    }

    /**
     * @test
     */
    public function it_should_use_empty_string_when_biller_response_is_json_but_missing_required_fields_to_create_mapping_criteria()
    {
        $billerResponse = $this->createMock(BillerResponse::class);

        $billerResponse->method('responsePayload')->willReturn(
            json_encode([
                'randomResponseFieldOne'   => 'test'
            ])
        );

        $mappingCriteria = MappingCriteriaNetbilling::create($billerResponse);

        $this->assertEmpty($mappingCriteria->processor());
        $this->assertEmpty($mappingCriteria->authMessage());
    }
}
