<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\BillerSettingsObfuscatorFactory;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingChargeSettings;
use ProBillerNG\Transaction\Domain\Model\NetbillingBillerSettingsObfuscator;
use ProBillerNG\Transaction\Domain\Model\PumapayBillerSettingsObfuscator;
use ProBillerNG\Transaction\Domain\Model\PumaPayChargeSettings;
use ProBillerNG\Transaction\Domain\Model\RocketgateBillerSettingsObfuscator;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use Tests\UnitTestCase;

class BillerSettingsObfuscatorFactoryTest extends UnitTestCase
{
    /**
     * @test
     */
    public function it_should_create_a_rocketgate_obfuscator_if_the_biller_settings_is_rocketgate()
    {
        /** @var BillerSettings $billerSettings */
        $billerSettings = $this->prophesize(RocketGateChargeSettings::class);
        $billerSettings->billerName()->willReturn(BillerSettings::ROCKETGATE);
        $obfuscator = BillerSettingsObfuscatorFactory::factory($billerSettings->reveal()->billerName());
        $this->assertInstanceOf(RocketgateBillerSettingsObfuscator::class, $obfuscator);
    }

    /**
     * @test
     */
    public function it_should_create_a_pumapay_obfuscator_if_the_biller_settings_is_pumapay()
    {
        /** @var BillerSettings $billerSettings */
        $billerSettings = $this->prophesize(PumaPayChargeSettings::class);
        $billerSettings->billerName()->willReturn(BillerSettings::PUMAPAY);
        $obfuscator = BillerSettingsObfuscatorFactory::factory($billerSettings->reveal()->billerName());
        $this->assertInstanceOf(PumapayBillerSettingsObfuscator::class, $obfuscator);
    }

    /**
     * @test
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ProBillerNG\Transaction\Domain\Model\Exception\BillerSettingObfuscatorNotDefined
     */
    public function it_should_create_a_netbilling_obfuscator_if_the_biller_settings_is_netbilling(): void
    {
        /** @var BillerSettings $billerSettings */
        $billerSettings = $this->prophesize(NetbillingChargeSettings::class);
        $billerSettings->billerName()->willReturn(BillerSettings::NETBILLING);
        $obfuscator = BillerSettingsObfuscatorFactory::factory($billerSettings->reveal()->billerName());
        $this->assertInstanceOf(NetbillingBillerSettingsObfuscator::class, $obfuscator);
    }
}
