<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

abstract class PumaPayBillerSettings implements BillerSettings, ObfuscatedData
{
    /**
     * @var string
     */
    protected $businessId;

    /**
     * @var string
     */
    protected $businessModel;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @return string
     */
    public function businessId(): string
    {
        return $this->businessId;
    }

    /**
     * @return string
     */
    public function businessModel(): string
    {
        return $this->businessModel;
    }

    /**
     * @return string
     */
    public function apiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function billerName(): string
    {
        return self::PUMAPAY;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'businessId'    => $this->businessId(),
            'businessModel' => $this->businessModel(),
            'apiKey'        => $this->apiKey(),
        ];
    }
}
