<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\ObfuscatedData;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RocketgateBillerSettingsObfuscator;
use Tests\UnitTestCase;

class RocketGateBillerSettingsTest extends UnitTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RocketGateBillerSettings
     */
    private $settings;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $merchantPassword;

    /**
     * @var string
     */
    private $merchantCustomerId;

    /**
     * @var string
     */
    private $merchantInvoiceId;

    /** @var RocketgateBillerSettingsObfuscator */
    private $obfuscator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obfuscator = new RocketgateBillerSettingsObfuscator();
        $this->merchantId         = $this->faker->uuid;
        $this->merchantPassword   = $this->faker->password;
        $this->merchantCustomerId = $this->faker->uuid;
        $this->merchantInvoiceId  = $this->faker->uuid;

        $this->settings = $this->getMockForAbstractClass(RocketGateBillerSettings::class, [
            $this->merchantId,
            $this->merchantPassword,
            $this->merchantCustomerId,
            $this->merchantInvoiceId,
            null
        ]);
    }

    /**
     * @test
     * @return array
     */
    public function it_should_return_an_array_when_requested_to_export_to_array(): array
    {
        $toArray = $this->settings->toArray();
        $this->assertIsArray($toArray);

        return $toArray;
    }

    /**
     * @test
     * @depends it_should_return_an_array_when_requested_to_export_to_array
     * @param array $toArray
     * @return void
     */
    public function array_should_contain_merchant_id(array $toArray): void
    {
        $this->assertArrayHasKey('merchantId', $toArray);
    }

    /**
     * @test
     * @depends it_should_return_an_array_when_requested_to_export_to_array
     * @param array $toArray
     * @return void
     */
    public function array_should_contain_merchant_password(array $toArray): void
    {
        $this->assertArrayHasKey('merchantPassword', $toArray);
    }

    /**
     * @test
     * @return void
     */
    public function merchant_password_in_array_should_match_settings_property(): void
    {
        $obfuscatedBillerSettings = $this->obfuscator->obfuscate($this->settings->toArray());
        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $obfuscatedBillerSettings['merchantPassword']);
    }

    /**
     * @test
     * @depends it_should_return_an_array_when_requested_to_export_to_array
     * @param array $toArray
     * @return void
     */
    public function array_should_contain_merchant_customer_id(array $toArray): void
    {
        $this->assertArrayHasKey('merchantCustomerId', $toArray);
    }

    /**
     * @test
     * @depends it_should_return_an_array_when_requested_to_export_to_array
     * @param array $toArray
     * @return void
     */
    public function array_should_contain_merchant_invoice_id(array $toArray): void
    {
        $this->assertArrayHasKey('merchantInvoiceId', $toArray);
    }

    /**
     * @test
     * @return void
     */
    public function merchant_id_in_array_should_match_settings_property(): void
    {
        $this->assertEquals($this->merchantId, $this->settings->toArray()['merchantId']);
    }

    /**
     * @test
     * @return void
     */
    public function merchant_customer_id_in_array_should_match_settings_property(): void
    {
        $this->assertEquals($this->merchantCustomerId, $this->settings->toArray()['merchantCustomerId']);
    }

    /**
     * @test
     * @return void
     */
    public function merchant_invoice_id_in_array_should_match_settings_property(): void
    {
        $this->assertEquals($this->merchantInvoiceId, $this->settings->toArray()['merchantInvoiceId']);
    }
}