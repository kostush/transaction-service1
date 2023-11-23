<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Netbilling\Application\Services\CancelRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\ChargeAdapterInterface;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;

interface BaseNetbillingCancelRebillAdapter extends ChargeAdapterInterface
{
    /**
     * @param CancelRebillCommand $command
     * @param \DateTimeImmutable  $requestDate
     * @return NetbillingBillerResponse
     */
    public function cancel(
        CancelRebillCommand $command,
        \DateTimeImmutable $requestDate
    ): NetbillingBillerResponse;
}