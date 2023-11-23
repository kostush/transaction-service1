<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;

interface LegacyNewSaleAdapter
{
    /**
     * @param ChargeTransaction $transaction Transaction
     * @param ChargesCollection $charges     Charges
     * @param Member            $member      Member
     * @return LegacyNewSaleBillerResponse
     */
    public function newSale(
        ChargeTransaction $transaction,
        ChargesCollection $charges,
        ?Member $member
    ): LegacyNewSaleBillerResponse;
}
