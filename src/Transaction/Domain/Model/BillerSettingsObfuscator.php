<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

interface BillerSettingsObfuscator
{
    /**
     * @param array $billerSettings Biller settings
     * @return array
     */
    public static function obfuscate(array $billerSettings): array;
}
