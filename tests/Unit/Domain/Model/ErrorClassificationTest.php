<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\ErrorClassification;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaNetbilling;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteriaRocketgate;
use Tests\UnitTestCase;

class ErrorClassificationTest extends UnitTestCase
{
    /**
     * @test
     * return array
     */
    public function it_should_return_default_error_classification_when_decline_biller_response_extra_data_netbilling_is_null() : array
    {
        $errorClassification = new ErrorClassification(
            $this->createMock(MappingCriteriaNetbilling::class),
            null
        );

        $this->assertInstanceOf(ErrorClassification::class, $errorClassification);

        return $errorClassification->toArray();
    }

    /**
     * @test
     *
     * @depends it_should_return_default_error_classification_when_decline_biller_response_extra_data_netbilling_is_null
     *
     * @param array $errorClassification
     */
    public function error_classification_should_contain_default_groupDecline(array $errorClassification) : void
    {
        $this->assertEquals(
            ErrorClassification::DEFAULT_GROUP_DECLINE,
            $errorClassification['errorClassification']['groupDecline']
        );
    }

    /**
     * @test
     *
     * @depends it_should_return_default_error_classification_when_decline_biller_response_extra_data_netbilling_is_null
     *
     * @param array $errorClassification
     */
    public function error_classification_should_contain_default_errorType(array $errorClassification) : void
    {
        $this->assertEquals(
            ErrorClassification::DEFAULT_ERROR_TYPE,
            $errorClassification['errorClassification']['errorType']
        );
    }

    /**
     * @test
     *
     * @depends it_should_return_default_error_classification_when_decline_biller_response_extra_data_netbilling_is_null
     *
     * @param array $errorClassification
     */
    public function error_classification_should_contain_default_groupMessage(array $errorClassification) : void
    {
        $this->assertEquals(
            ErrorClassification::DEFAULT_GROUP_MESSAGE,
            $errorClassification['errorClassification']['groupMessage']
        );
    }

    /**
     * @test
     *
     * @depends it_should_return_default_error_classification_when_decline_biller_response_extra_data_netbilling_is_null
     *
     * @param array $errorClassification
     */
    public function error_classification_should_contain_default_recommendedAction(array $errorClassification) : void
    {
        $this->assertEquals(
            ErrorClassification::DEFAULT_RECOMMENDED_ACTION,
            $errorClassification['errorClassification']['recommendedAction']
        );
    }

    /**
     * @test
     * return array
     */
    public function it_should_return_default_error_classification_when_decline_biller_response_extra_data_rocketgate_is_null() : array
    {
        $errorClassification = new ErrorClassification(
            $this->createMock(MappingCriteriaRocketgate::class),
            null
        );

        $this->assertInstanceOf(ErrorClassification::class, $errorClassification);

        return $errorClassification->toArray();
    }

    /**
     * @test
     *
     * @depends it_should_return_default_error_classification_when_decline_biller_response_extra_data_rocketgate_is_null
     *
     * @param array $errorClassification
     */
    public function error_classification_should_contain_default_values(array $errorClassification) : void
    {
        $this->assertEquals(
            ErrorClassification::DEFAULT_GROUP_DECLINE,
            $errorClassification['errorClassification']['groupDecline']
        );

        $this->assertEquals(
            ErrorClassification::DEFAULT_ERROR_TYPE,
            $errorClassification['errorClassification']['errorType']
        );

        $this->assertEquals(
            ErrorClassification::DEFAULT_GROUP_MESSAGE,
            $errorClassification['errorClassification']['groupMessage']
        );

        $this->assertEquals(
            ErrorClassification::DEFAULT_RECOMMENDED_ACTION,
            $errorClassification['errorClassification']['recommendedAction']
        );
    }


}