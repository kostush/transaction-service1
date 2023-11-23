<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse;

/**
 * Class DeclinedBillerResponseExtraDataRocketgate
 * @package ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse
 */
class DeclinedBillerResponseExtraDataRocketgate extends DeclinedBillerResponseExtraData
{
    /**
     * @var string
     */
    protected $reasonCode;

    /**
     * @var string
     */
    protected $bankResponseCode;

    /**
     * @param string $reasonCode
     */
    public function setReasonCode(string $reasonCode): void
    {
        $this->reasonCode = $reasonCode;
    }

    /**
     * @param string $bankResponseCode
     */
    public function setBankResponseCode(string $bankResponseCode): void
    {
        $this->bankResponseCode = $bankResponseCode;
    }

    /**
     * @return string
     */
    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }

    /**
     * @return string
     */
    public function getBankResponseCode(): string
    {
        return $this->bankResponseCode;
    }
}
