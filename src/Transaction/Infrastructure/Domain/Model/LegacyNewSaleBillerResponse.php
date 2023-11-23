<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use Exception;
use ProBillerNG\Transaction\Code;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;

class LegacyNewSaleBillerResponse extends LegacyBillerResponse
{
    const SUCCESS_CODE    = '200';
    const NO_REASON       = '';
    const NO_REDIRECT_URL = null;

    /**
     * @var string|null
     */
    private $redirectUrl;

    /**
     * LegacyNewSaleBillerResponse constructor.
     * @param string|null        $redirectUrl     Redirect Url
     * @param int                $status          Status
     * @param string             $code            Code
     * @param string             $reason          Reason
     * @param string|null        $requestPayload  Request Payload
     * @param \DateTimeImmutable $requestDate     Request Date
     * @param string|null        $responsePayload Response Payload
     * @param \DateTimeImmutable $responseDate    Response Date
     */
    private function __construct(
        ?string $redirectUrl,
        int $status,
        string $code,
        string $reason,
        ?string $requestPayload,
        \DateTimeImmutable $requestDate,
        ?string $responsePayload,
        \DateTimeImmutable $responseDate
    ) {
        $this->redirectUrl = $redirectUrl;
        parent::__construct(
            $status,
            $code,
            $reason,
            $requestPayload,
            $requestDate,
            $responsePayload,
            $responseDate
        );
    }

    /**
     * @param string             $redirectUrl     Redirect Url
     * @param string             $responsePayload Response Payload
     * @param string             $requestPayload  Request Payload
     * @param \DateTimeImmutable $requestDate     Request Date
     * @param \DateTimeImmutable $responseDate    Response Date
     * @return LegacyNewSaleBillerResponse
     */
    public static function createSuccessResponse(
        string $redirectUrl,
        string $responsePayload,
        ?string $requestPayload,
        \DateTimeImmutable $requestDate,
        \DateTimeImmutable $responseDate
    ): self {
        return new static(
            $redirectUrl,
            self::CHARGE_RESULT_PENDING,
            self::SUCCESS_CODE,
            self::NO_REASON,
            $requestPayload,
            $requestDate,
            $responsePayload,
            $responseDate
        );
    }

    /**
     * @param Exception|null $exception Exception
     * @return LegacyNewSaleBillerResponse
     * @throws Exception
     */
    public static function createAbortedResponse(?Exception $exception): self
    {
        $code    = $exception ? $exception->getCode() : Code::LEGACY_CIRCUIT_BREAKER_OPEN;
        $message = $exception ? $exception->getMessage() : Code::getMessage(
            Code::LEGACY_CIRCUIT_BREAKER_OPEN
        );

        return new static(
            self::NO_REDIRECT_URL,
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
