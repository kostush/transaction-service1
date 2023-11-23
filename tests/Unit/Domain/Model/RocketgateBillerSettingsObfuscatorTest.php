<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\ObfuscatedData;
use ProBillerNG\Transaction\Domain\Model\RocketgateBillerSettingsObfuscator;
use Tests\UnitTestCase;

class RocketgateBillerSettingsObfuscatorTest extends UnitTestCase
{
    /** @var RocketgateBillerSettingsObfuscator */
    private $obfuscator;

    /** @var array */
    private $billerSettings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obfuscator = new RocketgateBillerSettingsObfuscator();
        $this->billerSettings = [
            'merchant_password' => 'X',
            'some_other_key' => 'Y',
        ];
    }

    /**
     * @test
     * @return array
     */
    public function it_should_obfuscate_the_merchant_password()
    {
        $obfuscatedBillerSettings = $this->obfuscator->obfuscate($this->billerSettings);
        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $obfuscatedBillerSettings['merchantPassword']);
        return $obfuscatedBillerSettings;
    }

    /**
     * @param array $obfuscatedBillerSettings
     * @test
     * @depends it_should_obfuscate_the_merchant_password
     */
    public function it_should_not_change_other_fields(array $obfuscatedBillerSettings)
    {
        $originalBillerSettings = $this->billerSettings;
        unset($originalBillerSettings['merchantPassword']);
        foreach ($originalBillerSettings as $key => $value) {
            $this->assertEquals($value, $obfuscatedBillerSettings[$key]);
        }
    }
}