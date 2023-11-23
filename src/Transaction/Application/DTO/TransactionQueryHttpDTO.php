<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Transaction;

abstract class TransactionQueryHttpDTO implements \JsonSerializable
{
    /**
     * @var RetrieveTransactionReturnType
     */
    protected $transactionPayload;

    /**
     * TransactionQueryHttpDTO constructor.
     * @param Transaction $transaction Transaction
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->initTransaction($transaction);
    }

    /**
     * @param Transaction $transaction Transaction
     * @return void
     */
    abstract protected function initTransaction(Transaction $transaction): void;

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        /** @var Serializer $serializer */
        $serializer = SerializerBuilder::create()->build();

        return $serializer->toArray($this->transactionPayload);
    }
}
