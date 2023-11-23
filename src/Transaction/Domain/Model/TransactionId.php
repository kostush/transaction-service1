<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidTransactionInformationException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;

class TransactionId extends Id
{
    /**
     * @param Uuid|null $value Uuid
     * @return self
     * @throws \Exception
     */
    public static function create(Uuid $value = null): Id
    {
        return parent::create($value);
    }

    /**
     * @param string $value string
     * @return self
     * @throws InvalidTransactionInformationException
     * @throws \Exception
     */
    public static function createFromString(string $value): Id
    {
        try {
            return parent::createFromString($value);
        } catch (InvalidUuidStringException $e) {
            throw new InvalidTransactionInformationException('transactionId');
        }
    }
}
