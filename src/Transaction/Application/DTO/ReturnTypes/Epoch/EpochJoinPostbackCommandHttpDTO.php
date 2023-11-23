<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch;

use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochPostbackBillerResponse;

class EpochJoinPostbackCommandHttpDTO implements \JsonSerializable
{
    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var EpochPostbackBillerResponse
     */
    protected $billerResponse;

    /**
     * @param Transaction $transaction Transaction
     * @param EpochBillerResponse $billerResponse EpochBillerResponse object
     */
    public function __construct(Transaction $transaction, EpochBillerResponse $billerResponse)
    {
        $this->transaction = $transaction;
        $this->billerResponse = $billerResponse;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'status' => (string) $this->transaction->status(),
            'paymentType' => (string) $this->transaction->paymentType(),
            'paymentMethod' => (string) $this->billerResponse->paymentMethod(),
        ];
    }
}
