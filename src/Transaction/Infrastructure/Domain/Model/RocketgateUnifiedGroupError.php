<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Model;

use ErrorException;
use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\MapField;

/**
 * Class representation of the rocketgate unified error structure.
 */
class RocketgateUnifiedGroupError
{
    public const BILLER             = 'biller';
    public const BANK_RESPONSE_CODE = 'bankResponseCode';
    public const REASON_CODE        = 'reasonCode';

    /**
     * @var string
     */
    protected $biller = 'rocketgate';

    /**
     * @var string
     */
    protected $bankResponseCode;

    /**
     * @var string
     */
    protected $reasonCode;

    /**
     * @return MapField
     *
     * @throws ErrorException
     */
    public function generateMapField(): MapField
    {
        $mapField = new MapField(GPBType::STRING, GPBType::STRING);

        $mapField->offsetSet(self::BILLER, $this->biller);

        if ($this->hasBankResponseCode()) {
            $mapField->offsetSet(self::BANK_RESPONSE_CODE, $this->bankResponseCode);
        }

        if ($this->hasReasonCode()) {
            $mapField->offsetSet(self::REASON_CODE, $this->reasonCode);
        }

        return $mapField;
    }

    /**
     * @param string $bankResponseCode
     *
     * @return self
     */
    public function setBankResponseCode(string $bankResponseCode): self
    {
        $this->bankResponseCode = $bankResponseCode;

        return $this;
    }

    /**
     * @param string $reasonCode
     *
     * @return self
     */
    public function setReasonCode(string $reasonCode): self
    {
        $this->reasonCode = $reasonCode;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasBankResponseCode(): bool
    {
        return isset($this->bankResponseCode);
    }

    /**
     * @return bool
     */
    public function hasReasonCode(): bool
    {
        return isset($this->reasonCode);
    }
}
