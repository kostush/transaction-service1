<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\PrepaidInfoType;
use ProBillerNG\Transaction\Domain\Model\Transaction;

trait PrepaidInfoExtractorTrait
{
    use BillerResponseAttributeExtractorTrait;

    /**
     * @param Transaction $transaction Transaction.
     *
     * @return PrepaidInfoType
     */
    protected function prepaidInfoType(Transaction $transaction): PrepaidInfoType
    {
        return PrepaidInfoType::create(
            floatval($this->getAttribute($transaction, 'balanceAmount')),
            $this->getAttribute($transaction, 'balanceCurrency')
        );
    }
}
