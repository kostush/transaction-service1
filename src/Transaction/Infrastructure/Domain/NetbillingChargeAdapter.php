<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain;


use ProBillerNG\Netbilling\Application\Services\CreditCardChargeCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;

interface NetbillingChargeAdapter extends ChargeAdapterInterface
{
    /**
     * @param CreditCardChargeCommand $command
     * @param \DateTimeImmutable $requestDate
     * @return NetbillingBillerResponse
     */
    public function charge(
        CreditCardChargeCommand $command,
        \DateTimeImmutable $requestDate
    ): NetbillingBillerResponse;
}