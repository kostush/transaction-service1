<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Event;

use ProBillerNG\Logger\Log;

class TransactionAbortedEvent extends BaseEvent
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var string|null
     */
    private $reason;

    /**
     * @var string|null
     */
    private $previousTransactionId;

    /**
     * TransactionAbortedEvent constructor.
     * @param string                  $transactionType       The issuing class
     * @param string                  $aggregateRootId       Aggregate root id
     * @param string                  $status                Status
     * @param string|null             $code                  Reason code
     * @param string|null             $reason                Reason description
     * @param string|null             $previousTransactionId Previous transaction Id
     * @param \DateTimeImmutable|null $occurredOn            When the event occurred
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        string $transactionType,
        string $aggregateRootId,
        string $status,
        ?string $code,
        ?string $reason,
        ?string $previousTransactionId,
        ?\DateTimeImmutable $occurredOn
    ) {
        parent::__construct($aggregateRootId, $occurredOn, $transactionType);

        $this->transactionId         = $aggregateRootId;
        $this->status                = $status;
        $this->code                  = $code;
        $this->reason                = $reason;
        $this->previousTransactionId = $previousTransactionId;

        Log::debug('New event: Transaction Aborted', ['transactionId' => $aggregateRootId]);
    }

    /**
     * @return string
     */
    public function transactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function code(): ?string
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

    /**
     * @return string|null
     */
    public function previousTransactionId(): ?string
    {
        return $this->previousTransactionId;
    }
}
