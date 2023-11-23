<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch;

class EpochBillerTransaction
{
    /**
     * @var null|string
     */
    private $piCode;

    /**
     * @var null|string
     */
    private $billerTransactionId;

    /**
     * @var null|string
     */
    private $billerMemberId;

    /**
     * @var string
     */
    private $ans;

    /**
     * BillerTransaction constructor.
     * @param null|string $piCode              The Epoch product id
     * @param null|string $billerMemberId      The Epoch member id
     * @param null|string $billerTransactionId The Epoch transaction id
     * @param string      $ans                 The Epoch ans
     */
    public function __construct(
        ?string $piCode,
        ?string $billerMemberId,
        ?string $billerTransactionId,
        ?string $ans
    ) {
        $this->piCode              = $piCode;
        $this->billerMemberId      = $billerMemberId;
        $this->billerTransactionId = $billerTransactionId;
        $this->ans                 = $ans;
    }

    /**
     * @return null|string
     */
    public function piCode(): ?string
    {
        return $this->piCode;
    }

    /**
     * @return null|string
     */
    public function billerMemberId(): ?string
    {
        return $this->billerMemberId;
    }

    /**
     * @return null|string
     */
    public function billerTransactionId(): ?string
    {
        return $this->billerTransactionId;
    }

    /**
     * @return string
     */
    public function ans(): string
    {
        return $this->ans;
    }
}
