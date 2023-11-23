<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

interface TransactionRepository
{
    /**
     * Adds a transaction
     *
     * @param Transaction $transaction Transaction
     * @return void
     */
    public function add(Transaction $transaction);

    /**
     * Finds a transaction by id
     *
     * @param string $transactionId The transaction id
     * @return null|Transaction
     */
    public function findById(string $transactionId): ?Transaction;

    /**
     * Adds a transaction
     *
     * @param Transaction $transaction Transaction
     * @return void
     */
    public function update(Transaction $transaction);

    /**
     * Finds entities by a set of criteria.
     *
     * @param array      $criteria Criteria
     * @param array|null $orderBy  Order By
     * @param int|null   $limit    Limit
     * @param int|null   $offset   Offset
     *
     * @return array The objects.
     */
    public function findAllBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array;
}
