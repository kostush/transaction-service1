<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

interface CreditCardCharge
{
    /**
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse
     */
    public function chargeWithNewCreditCard(ChargeTransaction $transaction);

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse
     */
    public function chargeWithExistingCreditCard(ChargeTransaction $transaction);

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return mixed
     */
    public function suspendRebill(RebillUpdateTransaction $transaction);
}
