<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;


interface InMemoryRepository
{
    /**
     * @param string      $transactionId
     * @param string      $billerName
     * @param string|null $cvv
     */
    public function storeCvv(
        string $transactionId,
        string $billerName,
        ?string $cvv
    ): void;

    /**
     * @param   string      $transactionId
     * @param   string      $billerName
     * @return string|null
     */
    public function retrieveCvv(
        string $transactionId,
        string $billerName
    ): ?string;

    /**
     * @param string $transactionId
     * @param string $billerName
     */
    public function deleteCvv(
        string $transactionId,
        string $billerName
    ): void;

}