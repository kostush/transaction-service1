<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy;

use Illuminate\Http\Response;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\BillerResponseAttributeExtractorTrait;
use ProBillerNG\Transaction\Domain\Model\AbstractStatus;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;

class LegacyNewSaleCommandHttpDTO implements \JsonSerializable
{
    use BillerResponseAttributeExtractorTrait;

    public const REDIRECT_URL = 'redirectUrl';

    /**
     * @var AbstractStatus
     */
    protected $transactionStatus;

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
        $this->transactionStatus = $transaction->status();
        $this->response          = $this->buildResponseFromTransaction($transaction);
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return array
     * @throws \Exception
     */
    private function buildResponseFromTransaction(ChargeTransaction $transaction): array
    {
        $response['transactionId'] = (string) $transaction->transactionId();
        $response['status']        = (string) $transaction->status();

        if (!$transaction->status()->pending()) {
            $response['code']   = $transaction->code();
            $response['reason'] = $transaction->reason();
        }

        if ($transaction->status()->pending()) {
            $response['redirectUrl'] = $this->getAttribute($transaction, self::REDIRECT_URL);
        }

        $response['traceId']       = Log::getTraceId();
        $response['sessionId']     = Log::getSessionId();
        $response['correlationId'] = Log::getCorrelationId();

        return $response;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->response;
    }

    /**
     * @return int
     */
    public function responseStatus(): int
    {
        if ($this->transactionStatus->pending()) {
            return Response::HTTP_CREATED;
        }

        return Response::HTTP_OK;
    }
}
