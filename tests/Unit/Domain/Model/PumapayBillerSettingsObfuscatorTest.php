<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\ObfuscatedData;
use ProBillerNG\Transaction\Domain\Model\PumapayBillerSettingsObfuscator;
use ProBillerNG\Transaction\Domain\Model\RocketgateBillerSettingsObfuscator;
use Tests\UnitTestCase;

class PumapayBillerSettingsObfuscatorTest extends UnitTestCase
{
    const API_KEY = 'api_key';

    /** @var PumapayBillerSettingsObfuscator */
    private $obfuscator;

    /** @var array */
    private $billerSettings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obfuscator = new PumapayBillerSettingsObfuscator();
        $this->billerSettings = [
            self::API_KEY    => 'X',
            'some_other_key' => 'Y',
        ];
    }

    /**
     * @test
     * @return array
     */
    public function it_should_obfuscate_the_api_key(): array
    {
        $obfuscatedBillerSettings = $this->obfuscator->obfuscate($this->billerSettings);
        $this->assertEquals(ObfuscatedData::OBFUSCATED_STRING, $obfuscatedBillerSettings[self::API_KEY]);

        return $obfuscatedBillerSettings;
    }

    /**
     * @param array $obfuscatedBillerSettings
     * @test
     * @depends it_should_obfuscate_the_api_key
     * @return void
     */
    public function it_should_not_change_other_fields(array $obfuscatedBillerSettings): void
    {
        $originalBillerSettings = $this->billerSettings;
        unset($originalBillerSettings[self::API_KEY]);

        foreach ($originalBillerSettings as $key => $value) {
            $this->assertEquals($value, $obfuscatedBillerSettings[$key]);
        }
    }
}