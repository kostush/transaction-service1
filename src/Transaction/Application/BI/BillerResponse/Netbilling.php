<?php

namespace ProBillerNG\Transaction\Application\BI\BillerResponse;

use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Transaction;

/**
 * Class Netbilling
 * @package ProBillerNG\Transaction\Application\BI\BillerResponse
 */
class Netbilling
{
    /**
     * @param Transaction $transaction Transaction
     *
     * @return array
     */
    public static function getSpecificBillerFields(Transaction $transaction): array
    {
        $response = [];

        // call bin routing only if transaction type is 'charge'
        if ($transaction instanceof ChargeTransaction) {
            // bin routing
            $binRouting = $transaction->billerChargeSettings()->binRouting();

            if (!empty($binRouting)) {
                $response['binRouting'] = $binRouting;
            }
        }

        return $response;
    }
}
