<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;

class Rebill
{
    /**
     * @var int|null
     */
    protected $frequency;

    /**
     * @var int|null
     */
    protected $start;

    /**
     * @var Amount|null
     */
    protected $amount;

    /**
     * Rebill constructor.
     * @param int|null    $frequency Rebill frequency
     * @param int|null    $start     Start rebill
     * @param Amount|null $amount    Amount to rebill
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    protected function __construct(
        ?int $frequency,
        ?int $start,
        ?Amount $amount
    ) {
        $this->initFrequency($frequency);
        $this->initStart($start);
        $this->amount = $amount;
    }

    /**
     * @param int|null $frequency Frequency
     * @return void
     * @throws InvalidChargeInformationException
     */
    protected function initFrequency(?int $frequency): void
    {
        if (is_int($frequency) && !($frequency > 0)) {
            throw new InvalidChargeInformationException('rebill => frequency');
        }

        $this->frequency = (int) $frequency;
    }

    /**
     * @param int|null $start Start
     * @return void
     * @throws InvalidChargeInformationException
     */
    protected function initStart(?int $start): void
    {
        if (is_int($start) && $start <= 0) {
            throw new InvalidChargeInformationException('rebill => start');
        }

        $this->start = (int) $start;
    }

    /**
     * @param int|null    $frequency Frequency
     * @param int|null    $start     Start
     * @param Amount|null $amount    Amount
     * @return Rebill
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    public static function create(
        ?int $frequency,
        ?int $start,
        ?Amount $amount
    ): self {
        return new static(
            $frequency,
            $start,
            $amount
        );
    }

    /**
     * @param array|null $responsePayload Payload
     * @return Rebill|null
     * @throws InvalidChargeInformationException
     * @throws Exception
     * @throws \Exception
     */
    public static function createRebillFromLegacyResponsePayload(?array $responsePayload): ?self
    {
        if (!isset($responsePayload["rebill_days"])
            || !isset($responsePayload["initial_days"])
            || !isset($responsePayload["rebill_amount"])
        ) {
            return null;
        }

        return new static(
            (int) $responsePayload["rebill_days"],
            (int) $responsePayload["initial_days"],
            Amount::create(floatval($responsePayload["rebill_amount"])),
        );
    }

    /**
     * @param Rebill $rebill Rebill object
     * @return bool
     */
    public function equals(
        Rebill $rebill
    ): bool {
        return (
            ($this->amount()->equals($rebill->amount()))
            && ($this->frequency() === $rebill->frequency())
            && ($this->start() === $rebill->start())
        );
    }

    /**
     * @return Amount|null
     */
    public function amount(): ?Amount
    {
        return $this->amount;
    }

    /**
     * @return int|null
     */
    public function frequency(): ?int
    {
        return $this->frequency;
    }

    /**
     * @return int|null
     */
    public function start(): ?int
    {
        return $this->start;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'amount'    => ['value' => $this->amount()->value()],
            'frequency' => $this->frequency(),
            'start'     => $this->start(),
        ];
    }
}
