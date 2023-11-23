<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;

interface UpdateRebillService
{
    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return BillerResponse
     */
    public function start(RebillUpdateTransaction $transaction): BillerResponse;

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return BillerResponse
     */
    public function stop(RebillUpdateTransaction $transaction): BillerResponse;

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return mixed
     */
    public function update(RebillUpdateTransaction $transaction): BillerResponse;
}
