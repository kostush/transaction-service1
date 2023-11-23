<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;

interface LegacyService
{
    /**
     * @param ChargeTransaction $transaction Transaction
     * @param Member            $member      Member Member
     * @param ChargesCollection $charges     Charges
     * @return LegacyNewSaleBillerResponse
     */
    public function chargeNewSale(
        ChargeTransaction $transaction,
        ?Member $member,
        ChargesCollection $charges
    ): LegacyNewSaleBillerResponse;
}
