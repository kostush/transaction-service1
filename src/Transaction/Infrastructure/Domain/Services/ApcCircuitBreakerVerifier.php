<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Odesk\Phystrix\ApcStateStorage;
use ProBillerNG\Transaction\Domain\Services\CircuitBreakerService;

class ApcCircuitBreakerVerifier implements CircuitBreakerService
{
    /**
     * @param string $commandClass Biller Command Class
     * @return bool
     */
    public function isOpen(string $commandClass) : bool
    {
        return (boolean) apc_fetch(
            ApcStateStorage::CACHE_PREFIX . $commandClass . ApcStateStorage::OPENED_NAME
        );
    }
}
