<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy;

use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

class LegacyJoinPostbackCommandHttpDTO implements \JsonSerializable
{
    /**
     * @var ChargeTransaction
     */
    protected $transaction;

    /**
     * @var array
     */
    protected $response;

    /**
     * @param ChargeTransaction $transaction Transaction
     * @throws \Exception
     */
    public function __construct(ChargeTransaction $transaction)
    {
        $this->transaction = $transaction;
        $this->initTransaction();
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function initTransaction(): void
    {
        $this->response['transactionId'] = (string) $this->transaction->transactionId();
        $this->response['status']        = (string) $this->transaction->status();
        $this->response['paymentType']   = (string) $this->transaction->paymentType();
        $this->response['paymentMethod'] = (string) $this->transaction->paymentMethod();
        $this->response['traceId']       = Log::getTraceId();
        $this->response['sessionId']     = Log::getSessionId();
        $this->response['correlationId'] = Log::getCorrelationId();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->response;
    }
}
