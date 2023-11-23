<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\BillerSettingObfuscatorNotDefined;

class BillerSettingsObfuscatorFactory
{
    /**
     * @param string $billerName BillerName
     * @return BillerSettingsObfuscator
     * @throws BillerSettingObfuscatorNotDefined
     * @throws Exception
     */
    public static function factory(string $billerName): BillerSettingsObfuscator
    {
        $obfuscator = null;
        switch ($billerName) {
            case BillerSettings::ROCKETGATE:
                $obfuscator = new RocketgateBillerSettingsObfuscator();
                break;
            case BillerSettings::NETBILLING:
                $obfuscator = new NetbillingBillerSettingsObfuscator();
                break;
            case BillerSettings::PUMAPAY:
                $obfuscator = new PumapayBillerSettingsObfuscator();
                break;
            case BillerSettings::EPOCH:
                $obfuscator = new EpochBillerSettingsObfuscator();
                break;
            case BillerSettings::QYSSO:
                $obfuscator = new QyssoBillerSettingsObfuscator();
                break;
            default:
                throw new BillerSettingObfuscatorNotDefined($billerName);
        }

        return $obfuscator;
    }
}
