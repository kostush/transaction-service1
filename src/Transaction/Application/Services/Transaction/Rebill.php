<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Validators;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class Rebill
{
    use Validators;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var int
     */
    public $frequency;

    /**
     * @var int
     */
    public $start;

    /**
     * Rebill constructor.
     *
     * @param float $amount    Amount
     * @param int   $frequency Frequency
     * @param int   $start     Start
     *
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws MissingChargeInformationException
     */
    public function __construct($amount, $frequency, $start)
    {
        $this->initAmount($amount);
        $this->initFrequency($frequency);
        $this->initStart($start);
    }

    /**
     * @param float $amount Amount
     *
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    private function initAmount($amount)
    {
        if (empty($amount)) {
            throw new MissingChargeInformationException('rebill => amount');
        }

        if (!$this->isValidFloat($amount)) {
            throw new InvalidChargeInformationException('rebill => amount');
        }

        $this->amount = (float) $amount;
    }

    /**
     * @param mixed $frequency Frequency
     *
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws MissingChargeInformationException
     */
    private function initFrequency($frequency)
    {
        if (empty($frequency)) {
            throw new MissingChargeInformationException('rebill => frequency');
        }
        if (!$this->isValidInteger($frequency)) {
            throw new InvalidChargeInformationException('rebill => frequency');
        }
        $this->frequency = (int) $frequency;
    }

    /**
     * @param mixed $start Start
     *
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @throws MissingChargeInformationException
     */
    private function initStart($start)
    {
        if (empty($start)) {
            throw new MissingChargeInformationException('rebill => start');
        }
        if (!$this->isValidInteger($start)) {
            throw new InvalidChargeInformationException('rebill => start');
        }
        $this->start = (int) $start;
    }

    /**
     * @return float
     */
    public function amount(): float
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function frequency(): ?int
    {
        return $this->frequency;
    }

    /**
     * @return int
     */
    public function start(): int
    {
        return $this->start;
    }
}
