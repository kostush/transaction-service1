<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use Tests\UnitTestCase;

class RocketgateChargeSettingsTest extends UnitTestCase
{
    /**
     * @test
     * @return RocketGateChargeSettings
     * @throws InvalidMerchantInformationException
     * @throws Exception
     * @throws MissingMerchantInformationException
     */
    public function it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent(): RocketGateChargeSettings
    {
        $rocketgateChargeSettings = $this->createRocketgateChargeSettings();

        $this->assertInstanceOf(RocketGateChargeSettings::class, $rocketgateChargeSettings);

        return $rocketgateChargeSettings;
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     */
    public function it_should_contain_a_merchant_id(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotNull($rocketGateChargeSettings->merchantId());
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     */
    public function it_should_contain_a_merchant_password(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotNull($rocketGateChargeSettings->merchantPassword());
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     */
    public function it_should_contain_a_merchant_customer_id(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotNull($rocketGateChargeSettings->merchantCustomerId());
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     */
    public function it_should_contain_a_merchant_invoice_id(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotNull($rocketGateChargeSettings->merchantInvoiceId());
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     */
    public function it_should_contain_a_merchant_account(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotNull($rocketGateChargeSettings->merchantAccount());
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     */
    public function it_should_contain_a_merchant_site_id(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotNull($rocketGateChargeSettings->merchantSiteId());
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     */
    public function it_should_contain_a_merchant_product_id(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotNull($rocketGateChargeSettings->merchantProductId());
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     */
    public function it_should_contain_a_merchant_descriptor(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotNull($rocketGateChargeSettings->merchantDescriptor());
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     */
    public function it_should_contain_an_ip_address(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotNull($rocketGateChargeSettings->ipAddress());
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     * @throws InvalidMerchantInformationException
     * @throws Exception
     * @throws MissingMerchantInformationException
     */
    public function equals_should_return_true_when_comparing_two_rocketgate_charge_settings_objects_with_the_same_attributes(
        RocketGateChargeSettings $rocketGateChargeSettings
    ) {
        $secondRocketgateChargeSettings = $this->createRocketgateChargeSettings(
            [
                'merchantId'         => $rocketGateChargeSettings->merchantId(),
                'merchantPassword'   => $rocketGateChargeSettings->merchantPassword(),
                'merchantSiteId'     => $rocketGateChargeSettings->merchantSiteId(),
                'merchantCustomerId' => $rocketGateChargeSettings->merchantCustomerId(),
                'merchantInvoiceId'  => $rocketGateChargeSettings->merchantInvoiceId(),
                'merchantAccount'    => $rocketGateChargeSettings->merchantAccount(),
                'merchantProductId'  => $rocketGateChargeSettings->merchantProductId(),
                'merchantDescriptor' => $rocketGateChargeSettings->merchantDescriptor(),
                'ipAddress'          => $rocketGateChargeSettings->ipAddress(),
                'sharedSecret'       => $rocketGateChargeSettings->sharedSecret(),
                'simplified3DS'      => $rocketGateChargeSettings->simplified3DS(),
            ]
        );

        $this->assertEquals(true, $rocketGateChargeSettings->equals($secondRocketgateChargeSettings));
    }

    /**
     * @test
     * @depends it_should_create_a_rocketgate_charge_settings_object_when_correct_data_is_sent
     * @param RocketGateChargeSettings $rocketGateChargeSettings Charge Settings
     * @return void
     * @throws InvalidMerchantInformationException
     * @throws Exception
     * @throws MissingMerchantInformationException
     */
    public function equals_should_return_false_when_comparing_two_rocketgate_charge_settings_objects_with_different_attributes(
        RocketGateChargeSettings $rocketGateChargeSettings
    ) {
        $secondRocketgateChargeSettings = $this->createRocketgateChargeSettings(['ipAddress' => '127.0.2.2']);

        $this->assertEquals(false, $rocketGateChargeSettings->equals($secondRocketgateChargeSettings));
    }

    /**
     * @test
     * @return void
     * @throws InvalidMerchantInformationException
     * @throws Exception
     * @throws MissingMerchantInformationException
     */
    public function it_should_throw_an_invalid_argument_exception_if_an_invalid_ip_address_is_sent()
    {
        $this->expectException(InvalidMerchantInformationException::class);

        $this->createRocketgateChargeSettings(['ipAddress' => 'invalidIp']);
    }

    /**
     * @test
     * @return RocketGateChargeSettings
     * @throws InvalidMerchantInformationException
     * @throws Exception
     * @throws MissingMerchantInformationException
     */
    public function it_should_create_a_rocketgate_charge_settings_with_minimal_attributes()
    {
        $rocketgateChargeSettings = $this->createRocketgateChargeSettings(
            [
                'merchantSiteId'     => null,
                'merchantCustomerId' => "", // Empty string to not be replaced by faker value in test
                'merchantInvoiceId'  => "", // Empty string to not be replaced by faker value in test
                'merchantAccount'    => null,
                'merchantProductId'  => null,
                'merchantDescriptor' => null,
                'ipAddress'          => null
            ]
        );

        $this->assertInstanceOf(RocketGateChargeSettings::class, $rocketgateChargeSettings);

        return $rocketgateChargeSettings;
    }

    /**
     * @test
     * @param RocketGateChargeSettings $rocketGateChargeSettings Rocketgate charge settings
     * @depends it_should_create_a_rocketgate_charge_settings_with_minimal_attributes
     * @return void
     */
    public function it_should_add_merchant_customer_id_to_transaction_if_empty(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotEmpty($rocketGateChargeSettings->merchantCustomerId());
    }

    /**
     * @test
     * @param RocketGateChargeSettings $rocketGateChargeSettings Rocketgate charge settings
     * @depends it_should_create_a_rocketgate_charge_settings_with_minimal_attributes
     * @return void
     */
    public function generated_customer_id_should_be_32_characters_long(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertEquals(32, strlen($rocketGateChargeSettings->merchantCustomerId()));
    }

    /**
     * @test
     * @param RocketGateChargeSettings $rocketGateChargeSettings Rocketgate charge settings
     * @depends it_should_create_a_rocketgate_charge_settings_with_minimal_attributes
     * @return void
     */
    public function it_should_add_merchant_invoice_id_to_transaction_if_empty(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertNotEmpty($rocketGateChargeSettings->merchantInvoiceId());
    }

    /**
     * @test
     * @param RocketGateChargeSettings $rocketGateChargeSettings Rocketgate charge settings
     * @depends it_should_create_a_rocketgate_charge_settings_with_minimal_attributes
     * @return void
     */
    public function generated_invoice_id_should_be_32_characters_long(RocketGateChargeSettings $rocketGateChargeSettings)
    {
        $this->assertEquals(32, strlen($rocketGateChargeSettings->merchantInvoiceId()));
    }
}
