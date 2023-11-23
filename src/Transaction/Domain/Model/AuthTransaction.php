<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class AuthTransaction extends ChargeTransaction
{
    public const TYPE = 'auth';
}
