<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Logger\Log;
use DateTimeImmutable;

/**
 * Class QyssoReturnBillerResponse
 * @package ProBillerNG\Transaction\Infrastructure\Domain\Model
 */
class QyssoReturnBillerResponse extends QyssoBillerResponse
{
    /** @var string */
    private $paymentDetails;

    /** @var string */
    private $billerTransactionId;

    /**
     * @param int               $result          Result
     * @param string            $code            Code
     * @param string            $reason          Reason
     * @param string|null       $requestPayload  Request Payload
     * @param DateTimeImmutable $requestDate     Request Date
     * @param string|null       $responsePayload Response Payload
     * @param DateTimeImmutable $responseDate    Response Date
     */
    protected function __construct(
        int $result,
        string $code,
        string $reason,
        ?string $requestPayload,
        DateTimeImmutable $requestDate,
        ?string $responsePayload,
        DateTimeImmutable $responseDate
    ) {
        parent::__construct($result, $code, $reason, $requestPayload, $requestDate, $responsePayload, $responseDate);

        $response = json_decode($responsePayload, true);

        $this->billerTransactionId = $response['TransID'];
        $this->paymentDetails      = '';
    }

    /**
     * @param string $jsonResponse payload received from biller
     *
     * @return QyssoReturnBillerResponse
     * @throws LoggerException
     */
    public static function create(string $jsonResponse): QyssoReturnBillerResponse
    {
        $requestDate     = new DateTimeImmutable();
        $decodedResponse = json_decode($jsonResponse, true);

        Log::info('QyssoResponse', $decodedResponse);

        $code = (string) $decodedResponse['Reply'];

        return new static(
            static::mapStatus($code),
            $code,
            (string) $decodedResponse['ReplyDesc'],
            null,
            $requestDate,
            $jsonResponse,
            $requestDate
        );
    }
}
