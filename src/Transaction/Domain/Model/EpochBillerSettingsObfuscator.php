<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class EpochBillerSettingsObfuscator implements BillerSettingsObfuscator
{
    /**
     * @param array $billerSettings Biller Settings
     * @return array
     */
    public static function obfuscate(array $billerSettings): array
    {
        $billerSettings['clientKey']             = ObfuscatedData::OBFUSCATED_STRING;
        $billerSettings['clientVerificationKey'] = ObfuscatedData::OBFUSCATED_STRING;

        return $billerSettings;
    }
}
