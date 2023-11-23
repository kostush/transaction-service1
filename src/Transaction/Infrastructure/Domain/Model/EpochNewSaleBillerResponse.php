<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Epoch\Domain\Model\NewSaleTranslateResponse;
use ProBillerNG\Transaction\Code;

class EpochNewSaleBillerResponse extends EpochBillerResponse
{
    /**
     * @param int                $result          Result
     * @param string             $code            Code
     * @param string             $reason          Reason
     * @param string|null        $requestPayload  Request Payload
     * @param \DateTimeImmutable $requestDate     Request Date
     * @param string|null        $responsePayload Response Payload
     * @param \DateTimeImmutable $responseDate    Response Date
     */
    protected function __construct(
        int $result,
        string $code,
        string $reason,
        ?string $requestPayload,
        \DateTimeImmutable $requestDate,
        ?string $responsePayload,
        \DateTimeImmutable $responseDate
    ) {
        parent::__construct($result, $code, $reason, $requestPayload, $requestDate, $responsePayload, $responseDate);
    }

    /**
     * @param \DateTimeImmutable $requestDate  Request date
     * @param string             $jsonResponse Response
     * @param \DateTimeImmutable $responseDate Response date
     * @return self
     */
    public static function create(
        \DateTimeImmutable $requestDate,
        string $jsonResponse,
        \DateTimeImmutable $responseDate
    ): self {
        $decodedResponse = json_decode($jsonResponse, true);

        $requestPayload  = null;
        $responsePayload = null;

        $code   = '200';
        $reason = '';

        if (!empty($decodedResponse['reason'])) {
            // is an error
            $code   = isset($decodedResponse['code']) ? (string) $decodedResponse['code'] : '400';
            $reason = (string) $decodedResponse['reason'];
        }

        return new static(
            static::getStatus($decodedResponse),
            $code,
            $reason,
            isset($decodedResponse['request']) ? json_encode($decodedResponse['request']) : null,
            $requestDate,
            isset($decodedResponse['response']) ? json_encode($decodedResponse['response']) : null,
            $responseDate
        );
    }

    /**
     * @param array $decodedResponse Decoded Response
     * @return int
     */
    protected static function getStatus(array $decodedResponse): int
    {
        switch ($decodedResponse['status'] ?? null) {
            case NewSaleTranslateResponse::NEW_SALE_RESULT_APPROVED:
                $status = self::CHARGE_RESULT_PENDING;
                break;

            case NewSaleTranslateResponse::NEW_SALE_RESULT_DECLINED:
                $status = self::CHARGE_RESULT_DECLINED;
                break;

            default:
                $status = self::CHARGE_RESULT_ABORTED;
        }

        return $status;
    }

    /**
     * @param \Exception|null $exception Exception
     *
     * @return self
     * @throws \Exception
     */
    public static function createAbortedResponse(?\Exception $exception): self
    {
        $code    = $exception ? $exception->getCode() : Code::EPOCH_CIRCUIT_BREAKER_OPEN;
        $message = $exception ? $exception->getMessage() : Code::getMessage(
            Code::EPOCH_CIRCUIT_BREAKER_OPEN
        );

        return new static(
            self::CHARGE_RESULT_ABORTED,
            (string) $code,
            $message,
            null,
            new \DateTimeImmutable(),
            null,
            new \DateTimeImmutable()
        );
    }
}
