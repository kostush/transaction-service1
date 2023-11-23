<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class PumapayRebillUpdateSettings extends PumapayBillerSettings
{
    /**
     * PumapayRebillUpdateSettings constructor.
     * @param string $businessId    Business Id
     * @param string $businessModel Business model
     * @param string $apiKey        Api key
     */
    private function __construct(
        string $businessId,
        string $businessModel,
        string $apiKey
    ) {
        $this->businessId    = $businessId;
        $this->businessModel = $businessModel;
        $this->apiKey        = $apiKey;
    }

    /**
     * @param string $businessId    Business Id
     * @param string $businessModel Business model
     * @param string $apiKey        Api key
     * @return PumapayRebillUpdateSettings
     */
    public static function create(
        string $businessId,
        string $businessModel,
        string $apiKey
    ): self {
        return new static(
            $businessId,
            $businessModel,
            $apiKey
        );
    }
}
