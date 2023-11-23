<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use Tests\UnitTestCase;

class NetbillingChargeSettingsTest extends UnitTestCase
{
   /** @var int */
    protected $initialDays;

    /** @var string */
    protected $siteTag;

    /** @var string */
    protected $accountId;

    /** @var string|null */
    protected $browser;

    /** @var string|null */
    protected $host;

    /** @var string|null */
    protected $binRouting;

    /** @var string|null */
    protected $merchantPassword;

    /** @var boolean */
    protected $disableFraudChecks;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->initialDays      = 30;
        $this->siteTag          = 'site_tag';
        $this->accountId        = 'account_id';
        $this->browser          = 'browser';
        $this->host             = 'host';
        $this->binRouting       = 'bin_routing';
        $this->merchantPassword = 'merchant_password';
        $this->disableFraudChecks = true;
    }

    /**
     * @test
     * @throws LoggerException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     * @return NetbillingChargeSettings
     */
    public function it_should_create_netbilling_biller_settings_when_valid_data_is_provided(): NetbillingChargeSettings
    {
        $netbillingChargeSettings = NetbillingChargeSettings::create(
            $this->siteTag,
            $this->accountId,
            $this->merchantPassword,
            $this->initialDays,
            $this->faker->ipv4,
            $this->browser,
            $this->host,
            '',
            $this->binRouting,
            null,
            $this->disableFraudChecks
        );
        $this->assertInstanceOf(NetbillingChargeSettings::class, $netbillingChargeSettings);
        return $netbillingChargeSettings;
    }

    /**
     * @test
     * @depends it_should_create_netbilling_biller_settings_when_valid_data_is_provided
     * @param NetbillingChargeSettings $netbillingChargeSettings Netbilling charge settings
     * @return void
     */
    public function created_netbilling_biller_settings_should_have_correct_browser(NetbillingChargeSettings $netbillingChargeSettings):void
    {
        $this->assertEquals($this->browser, $netbillingChargeSettings->browser());
    }

    /**
     * @test
     * @depends it_should_create_netbilling_biller_settings_when_valid_data_is_provided
     * @param NetbillingChargeSettings $netbillingChargeSettings Netbilling charge settings
     * @return void
     */
    public function created_netbilling_biller_settings_should_have_correct_host(NetbillingChargeSettings $netbillingChargeSettings):void
    {
        $this->assertEquals($this->host, $netbillingChargeSettings->host());
    }

    /**
     * @test
     * @depends it_should_create_netbilling_biller_settings_when_valid_data_is_provided
     * @param NetbillingChargeSettings $netbillingChargeSettings Netbilling charge settings
     * @return void
     */
    public function created_netbilling_biller_settings_should_have_correct_bin_routing(NetbillingChargeSettings $netbillingChargeSettings):void
    {
        $this->assertEquals($this->binRouting, $netbillingChargeSettings->binRouting());
    }

    /**
     * @test
     * @depends it_should_create_netbilling_biller_settings_when_valid_data_is_provided
     * @param NetbillingChargeSettings $netbillingChargeSettings Netbilling charge settings
     * @return void
     */
    public function created_netbilling_biller_settings_should_have_correct_account_id(NetbillingChargeSettings $netbillingChargeSettings):void
    {
        $this->assertEquals($this->accountId, $netbillingChargeSettings->accountId());
    }

    /**
     * @test
     * @depends it_should_create_netbilling_biller_settings_when_valid_data_is_provided
     * @param NetbillingChargeSettings $netbillingChargeSettings Netbilling charge settings
     * @return void
     */
    public function created_netbilling_biller_settings_should_have_correct_site_tag(NetbillingChargeSettings $netbillingChargeSettings):void
    {
        $this->assertEquals($this->siteTag, $netbillingChargeSettings->siteTag());
    }

    /**
     * @test
     * @depends it_should_create_netbilling_biller_settings_when_valid_data_is_provided
     * @param NetbillingChargeSettings $netbillingChargeSettings Netbilling charge settings
     * @return void
     */
    public function created_netbilling_biller_settings_should_have_correct_merchant_password(NetbillingChargeSettings $netbillingChargeSettings):void
    {
        $this->assertEquals($this->merchantPassword, $netbillingChargeSettings->merchantPassword());
    }

    /**
     * @test
     * @depends it_should_create_netbilling_biller_settings_when_valid_data_is_provided
     * @param NetbillingChargeSettings $netbillingChargeSettings Netbilling charge settings
     * @return void
     */
    public function created_netbilling_biller_settings_should_have_correct_disable_fraud_checks(NetbillingChargeSettings $netbillingChargeSettings):void
    {
        $this->assertEquals($this->disableFraudChecks, $netbillingChargeSettings->disableFraudChecks());
    }


    /**
     * @test
     * @throws LoggerException
     * @throws InvalidMerchantInformationException
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     * @throws MissingMerchantInformationException
     *
     */
    public function it_should_throw_exception_when_merchant_password_is_empty(): void
    {
        $this->expectException(MissingMerchantInformationException::class);
        $netbillingChargeSettings = NetbillingChargeSettings::create(
            $this->siteTag,
            $this->accountId,
            null,
            $this->initialDays,
            $this->faker->ipv4,
            $this->browser,
            $this->host,
            '',
            $this->binRouting,
            null,
            $this->disableFraudChecks
        );
    }
}
