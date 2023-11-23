<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

abstract class PaymentInformation
{
    /**
     * @return array
     */
    abstract public function detailedInformation(): array;

    /**
     * @return array
     */
    abstract public function toArray(): array;
}
