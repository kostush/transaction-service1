<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class PumapayBillerSettingsObfuscator implements BillerSettingsObfuscator
{
    /**
     * @param array $billerSettings Biller Settings
     * @return array
     */
    public static function obfuscate(array $billerSettings): array
    {
        $billerSettings['api_key'] = ObfuscatedData::OBFUSCATED_STRING;
        return $billerSettings;
    }
}
