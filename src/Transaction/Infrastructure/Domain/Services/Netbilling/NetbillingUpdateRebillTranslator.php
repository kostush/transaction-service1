<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;

interface NetbillingUpdateRebillTranslator
{
    /**
     * @param RebillUpdateTransaction $transaction rebill update transaction
     * @return \ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function update(RebillUpdateTransaction $transaction);
}
