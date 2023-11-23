<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\Event;

use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\BillerInteraction;

class TransactionDeclinedEvent extends BaseEvent
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
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $billerInteractionId;

    /**
     * @var string
     */
    private $billerInteractionType;

    /**
     * @var string json
     */
    private $billerInteractionPayload;

    /**
     * @var \DateTimeImmutable
     */
    private $billerInteractionCreatedAt;

    /**
     * @var string|null
     */
    private $previousTransactionId;

    /**
     * @var string
     */
    private $paymentType;

    /**
     * @var string|null
     */
    private $paymentMethod;

    /**
     * @var int|null
     */
    private $threedsVersion;

    /**
     * TransactionDeclinedEvent constructor.
     *
     * @param string                  $transactionType       The issuing class
     * @param string                  $aggregateId           Aggregate Id
     * @param string                  $status                status
     * @param string                  $code                  code
     * @param string                  $reason                reason
     * @param BillerInteraction       $billerInteraction     the billerInteraction created
     * @param string|null             $previousTransactionId Previous transaction Id
     * @param \DateTimeImmutable|null $occurredOn            Occurred On
     * @param string|null             $paymentType           The payment type used
     * @param string|null             $paymentMethod         Payment method
     * @param int|null                $threedsVersion        Threeds version
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        string $transactionType,
        ?string $aggregateId,
        string $status,
        string $code,
        string $reason,
        BillerInteraction $billerInteraction,
        ?string $previousTransactionId,
        ?\DateTimeImmutable $occurredOn,
        ?string $paymentType,
        ?string $paymentMethod,
        ?int $threedsVersion
    ) {
        parent::__construct($aggregateId, $occurredOn, $transactionType);
        $this->transactionId              = $aggregateId;
        $this->status                     = $status;
        $this->code                       = $code;
        $this->reason                     = $reason;
        $this->billerInteractionId        = (string) $billerInteraction->billerInteractionId();
        $this->billerInteractionType      = $billerInteraction->type();
        $this->billerInteractionPayload   = $billerInteraction->payload();
        $this->billerInteractionCreatedAt = $billerInteraction->createdAt();
        $this->previousTransactionId      = $previousTransactionId;
        $this->paymentType                = $paymentType;
        $this->paymentMethod              = $paymentMethod;
        $this->threedsVersion             = $threedsVersion;

        Log::debug('New event: Transaction Declined', ['transactionId' => $this->transactionId,]);
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
    public function billerInteractionId(): string
    {
        return $this->billerInteractionId;
    }

    /**
     * @return string
     */
    public function billerInteractionType(): string
    {
        return $this->billerInteractionType;
    }

    /**
     * @return string
     */
    public function billerInteractionPayload(): string
    {
        return $this->billerInteractionPayload;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function billerInteractionCreatedAt(): \DateTimeImmutable
    {
        return $this->billerInteractionCreatedAt;
    }

    /**
     * @return string|null
     */
    public function previousTransactionId(): ?string
    {
        return $this->previousTransactionId;
    }

    /**
     * @return string|null
     */
    public function paymentType(): ?string
    {
        return $this->paymentType;
    }

    /**
     * @return string|null
     */
    public function paymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @return int|null
     */
    public function threedsVersion(): ?int
    {
        return $this->threedsVersion;
    }
}
