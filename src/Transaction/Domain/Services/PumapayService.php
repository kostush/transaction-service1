<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayCancelRebillBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\PumapayPostbackBillerResponse;

interface PumapayService
{
    /**
     * @param ChargeTransaction $transaction ChargeTransaction
     * @return PumapayBillerResponse
     */
    public function retrieveQrCode(ChargeTransaction $transaction): PumapayBillerResponse;

    /**
     * @param string $payload         Payload
     * @param string $transactionType Transaction Type
     * @return PumapayPostbackBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function translatePostback(string $payload, string $transactionType): PumapayPostbackBillerResponse;

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string            $businessId  Business Id
     * @param string            $apiKey      Api Key
     * @return PumapayCancelRebillBillerResponse
     */
    public function cancelRebill(
        ChargeTransaction $transaction,
        string $businessId,
        string $apiKey
    ): PumapayCancelRebillBillerResponse;
}

