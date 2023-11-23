<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

interface CircuitBreakerService
{
    /**
     * @param string $commandClass Biller Command Class
     * @return bool
     */
    public function isOpen(string $commandClass) : bool;
}
