<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;

interface ChargeService
{
    /**
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse
     */
    public function chargeNewCreditCard(ChargeTransaction $transaction);

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse
     */
    public function chargeOtherPaymentType(ChargeTransaction $transaction);

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse
     */
    public function chargeExistingCreditCard(ChargeTransaction $transaction);

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return mixed
     */
    public function suspendRebill(RebillUpdateTransaction $transaction);

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string|null       $pares       Pares
     * @param string|null       $md          Biller transaction id
     * @param string|null       $cvv         CVV retrieved from Redis
     * @return BillerResponse
     */
    public function completeThreeDCreditCard(
        ChargeTransaction $transaction,
        ?string $pares,
        ?string $md,
        ?string $cvv = null
    ): BillerResponse;


    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string            $queryString Query string
     * @return BillerResponse
     */
    public function simplifiedCompleteThreeD(
        ChargeTransaction $transaction,
        string $queryString
    ): BillerResponse;
}
