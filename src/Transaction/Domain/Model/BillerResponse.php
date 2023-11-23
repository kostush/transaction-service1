<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

abstract class BillerResponse
{
    public const CHARGE_RESULT_APPROVED = 1;
    public const CHARGE_RESULT_DECLINED = 2;
    public const CHARGE_RESULT_ABORTED  = 3;
    public const CHARGE_RESULT_PENDING  = 4;

    /**
     * @var int
     */
    protected $result;

    /**
     * @var int
     */
    protected $code;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var string
     */
    protected $requestPayload;

    /**
     * @var \DateTimeImmutable
     */
    protected $requestDate;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $responseDate;

    /**
     * @var string
     */
    protected $responsePayload;

    /**
     * CreditCardBillerResponse constructor.
     * @param int                $result          Result
     * @param string             $code            Code
     * @param string             $reason          Reason
     * @param string|null        $requestPayload  Raw Request
     * @param \DateTimeImmutable $requestDate     Request date
     * @param string|null        $responsePayload Raw Response
     * @param \DateTimeImmutable $responseDate    Response date
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
        $this->result          = $result;
        $this->code            = $code;
        $this->reason          = $reason;
        $this->requestPayload  = $requestPayload;
        $this->requestDate     = $requestDate;
        $this->responsePayload = $responsePayload;
        $this->responseDate    = $responseDate;
    }

    /**
     * @return int
     */
    public function result(): int
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function reason(): string
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function requestPayload(): ?string
    {
        return $this->requestPayload;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function requestDate(): ?\DateTimeImmutable
    {
        return $this->requestDate;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function responseDate(): ?\DateTimeImmutable
    {
        return $this->responseDate;
    }

    /**
     * @return string
     */
    public function responsePayload(): ?string
    {
        return $this->responsePayload;
    }

    /**
     * @return bool
     */
    public function approved(): bool
    {
        return $this->result() === self::CHARGE_RESULT_APPROVED;
    }

    /**
     * @return bool
     */
    public function declined(): bool
    {
        return $this->result() === self::CHARGE_RESULT_DECLINED;
    }

    /**
     * @return bool
     */
    public function aborted(): bool
    {
        return $this->result() === self::CHARGE_RESULT_ABORTED;
    }

    /**
     * @return bool
     */
    public function pending(): bool
    {
        return $this->result() === self::CHARGE_RESULT_PENDING;
    }

    /**
     * @return bool
     */
    public function shouldRetryWithoutThreeD(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isNsfTransaction(): bool
    {
        return false;
    }
}
