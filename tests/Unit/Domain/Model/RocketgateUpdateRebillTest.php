<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\RocketGateRebillUpdateSettings;
use Tests\UnitTestCase;

class RocketgateUpdateRebillTest extends UnitTestCase
{
    /**
     * @test
     * @return RocketGateRebillUpdateSettings
     * @throws \Exception
     */
    public function it_should_return_an_object_when_correct_data_is_provided(): RocketGateRebillUpdateSettings
    {
        $rocketgateUpdateSettings = $this->createRocketGateRebillUpdateSettings();

        $this->assertInstanceOf(RocketGateRebillUpdateSettings::class, $rocketgateUpdateSettings);

        return $rocketgateUpdateSettings;
    }

    /**
     * @test
     * @depends it_should_return_an_object_when_correct_data_is_provided
     * @param RocketGateRebillUpdateSettings $rocketgateUpdateSettings Update Settings
     * @return void
     */
    public function it_should_contain_a_merchant_id(RocketGateRebillUpdateSettings $rocketgateUpdateSettings)
    {
        $this->assertNotNull($rocketgateUpdateSettings->merchantId());
    }

    /**
     * @test
     * @depends it_should_return_an_object_when_correct_data_is_provided
     * @param RocketGateRebillUpdateSettings $rocketgateUpdateSettings Update Settings
     * @return void
     */
    public function it_should_contain_a_merchant_password(RocketGateRebillUpdateSettings $rocketgateUpdateSettings)
    {
        $this->assertNotNull($rocketgateUpdateSettings->merchantPassword());
    }

    /**
     * @test
     * @depends it_should_return_an_object_when_correct_data_is_provided
     * @param RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings Update Settings
     * @return void
     */
    public function it_should_contain_a_merchant_customer_id(
        RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings
    ) {
        $this->assertNotNull($rocketGateCancelRebillSettings->merchantCustomerId());
    }

    /**
     * @test
     * @depends it_should_return_an_object_when_correct_data_is_provided
     * @param RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings Update Settings
     * @return void
     */
    public function it_should_contain_a_merchant_invoice_id(
        RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings
    ) {
        $this->assertNotNull($rocketGateCancelRebillSettings->merchantInvoiceId());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_contain_a_merchant_account_when_provided(): void
    {
        $rocketgateUpdateSettings = $this->createRocketGateRebillUpdateSettings(
            [
                'merchantAccount' => "1"
            ]
        );

        $this->assertSame("1", $rocketgateUpdateSettings->merchantAccount());
    }

    /**
     * @test
     * @depends it_should_return_an_object_when_correct_data_is_provided
     * @param RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings Update Settings
     * @return void
     * @throws \Exception
     */
    public function equals_should_return_true_when_comparing_two_rocketgate_cancel_rebill_settings_objects_with_the_same_attributes(
        RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings
    ) {
        $secondRocketgateChargeSettings = $this->createRocketGateRebillUpdateSettings(
            [
                'merchantId'         => $rocketGateCancelRebillSettings->merchantId(),
                'merchantPassword'   => $rocketGateCancelRebillSettings->merchantPassword(),
                'merchantCustomerId' => $rocketGateCancelRebillSettings->merchantCustomerId(),
                'merchantInvoiceId'  => $rocketGateCancelRebillSettings->merchantInvoiceId()
            ]
        );

        $this->assertEquals(true, $rocketGateCancelRebillSettings->equals($secondRocketgateChargeSettings));
    }

    /**
     * @test
     * @depends it_should_return_an_object_when_correct_data_is_provided
     * @param RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings Update Settings
     * @return void
     * @throws \Exception
     */
    public function equals_should_return_false_when_comparing_two_rocketgate_cancel_rebill_settings_objects_with_different_attributes(
        RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings
    ) {
        $secondRocketgateChargeSettings = $this->createRocketGateRebillUpdateSettings(['merchantId' => '4444']);

        $this->assertEquals(false, $rocketGateCancelRebillSettings->equals($secondRocketgateChargeSettings));
    }
}
