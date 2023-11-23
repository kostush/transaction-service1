<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class NetbillingBillerSettingsObfuscator implements BillerSettingsObfuscator
{
    /**
     * @param array $billerSettings Biller settings
     * @return array
     */
    public static function obfuscate(array $billerSettings): array
    {
        $billerSettings['accountId'] = ObfuscatedData::OBFUSCATED_STRING;
        return $billerSettings;
    }
}
