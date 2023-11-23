<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Pumapay;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class PumapayFirestoreSerializer
{
    /**
     * @param array $data
     *
     * @return Transaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    public static function createTransaction(array $data): Transaction
    {
        return ChargeTransaction::createSingleChargeOnPumapay(
            $data['siteId'] ?? null,
            $data['chargeInformation']['amount']['value'] ?? null,
            $data['billerName'],
            $data['chargeInformation']['currency']['code'],
            $data['billerChargeSettings']['businessId'],
            $data['billerChargeSettings']['businessModel'],
            $data['billerChargeSettings']['apiKey'],
            $data['billerChargeSettings']['title'],
            $data['billerChargeSettings']['description'],
            self::buildRebill($data['chargeInformation'])
        );
    }

    /**
     * @param $chargeInformation
     *
     * @return Rebill|null
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    private static function buildRebill($chargeInformation): ?Rebill
    {
        if (isset($chargeInformation['rebill'])) {
            return new Rebill(
                (float) $chargeInformation['rebill']['amount'],
                $chargeInformation['rebill']['frequency'],
                $chargeInformation['rebill']['start']
            );
        }

        return null;
    }
}