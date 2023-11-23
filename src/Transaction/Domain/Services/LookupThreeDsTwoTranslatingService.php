<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

interface LookupThreeDsTwoTranslatingService
{
    /**
     * @param ChargeTransaction $transaction            Previous transaction
     * @param string            $cardNumber             Card number
     * @param string            $expirationMonth        Expiration month
     * @param string            $expirationYear         Expiration year
     * @param string            $cvv                    Cvv
     * @param string            $deviceFingerprintingId Device fingerprinting id
     * @param string            $returnUrl              Return url
     * @param string            $merchantAccount        Merchant account
     * @return BillerResponse
     */
    public function performLookup(
        ChargeTransaction $transaction,
        string $cardNumber,
        string $expirationMonth,
        string $expirationYear,
        string $cvv,
        string $deviceFingerprintingId,
        string $returnUrl,
        string $merchantAccount
    ): BillerResponse;
}
