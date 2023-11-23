<?php

namespace ProBillerNG\Transaction\Application\BI\BillerResponse;

use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\PrepaidInfoType;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Transaction;

/**
 * Class Rocketgate
 * @package ProBillerNG\Transaction\Application\BI\BillerResponse
 */
class Rocketgate
{
    /**
     * @param Transaction    $transaction    Transaction
     * @param BillerResponse $billerResponse Biller response
     *
     * @return array
     */
    public static function getSpecificBillerFields(Transaction $transaction, ?BillerResponse $billerResponse): array
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
        if ($billerResponse instanceof BillerResponse) {
            $prepaidInfo = PrepaidInfoType::create(
                $billerResponse->balanceAmount(),
                $billerResponse->balanceCurrency()
            );

            if ($prepaidInfo->isAvailable()) {
                $response['prepaid'] = $prepaidInfo->toArray();
            }
        } else {
            Log::info(
                'GetSpecificBillerFields Biller response is not instace of BillerResponse.',
                ['BillerResponseIsNull' => is_null($billerResponse)]
            );
        }

        return $response;
    }
}
