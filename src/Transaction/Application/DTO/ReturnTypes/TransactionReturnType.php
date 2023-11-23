<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes;

abstract class TransactionReturnType
{
    /** @var string */
    protected $transactionId;

    /** @var string */
    protected $amount;

    /** @var string */
    protected $createdAt;

    /** @var string */
    protected $rebillAmount;

    /** @var string */
    protected $rebillFrequency;

    /** @var string */
    protected $rebillStart;

    /** @var string */
    protected $status;

    /** @var int|null */
    protected $code;

    /** @var string|null */
    protected $reason;

    /**
     * @param int $code Code
     * @return void
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @param string $reason Reason
     * @return void
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return int|null
     */
    public function code(): ?int
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function reason(): ?string
    {
        return $this->reason;
    }
}
