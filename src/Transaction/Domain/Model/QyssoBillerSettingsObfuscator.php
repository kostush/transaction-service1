<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class QyssoBillerSettingsObfuscator implements BillerSettingsObfuscator
{
    /**
     * @param array $billerSettings Biller Settings
     * @return array
     */
    public static function obfuscate(array $billerSettings): array
    {
        return $billerSettings;
    }
}
