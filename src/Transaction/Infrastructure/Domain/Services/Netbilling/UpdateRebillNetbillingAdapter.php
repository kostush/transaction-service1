<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Netbilling\Application\Services\UpdateRebillCommand;

interface UpdateRebillNetbillingAdapter
{
    /**
     * @param UpdateRebillCommand $command
     * @param \DateTimeImmutable  $requestDate
     * @return mixed
     */
    public function update(
        UpdateRebillCommand $command,
        \DateTimeImmutable $requestDate
    );
}