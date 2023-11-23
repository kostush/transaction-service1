<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

abstract class RocketGateBillerSettings implements BillerSettings, ObfuscatedData
{
    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $merchantPassword;

    /**
     * @var string
     */
    protected $merchantCustomerId;

    /**
     * @var string
     */
    protected $merchantInvoiceId;

    /**
     * @var string|null
     */
    protected $referringMerchantId;

    /**
     * RocketGateBillerSettings constructor.
     *
     * @param string      $merchantId         Merchant Id
     * @param string      $merchantPassword   Merchant Password
     * @param string      $merchantCustomerId Merchant Customer Id
     * @param string      $merchantInvoiceId  Merchant Invoice Id
     * @param string|null $referringMerchantId
     */
    public function __construct(
        string $merchantId,
        string $merchantPassword,
        string $merchantCustomerId,
        string $merchantInvoiceId,
        ?string $referringMerchantId
    ) {
        $this->merchantId          = $merchantId;
        $this->merchantPassword    = $merchantPassword;
        $this->merchantCustomerId  = $merchantCustomerId;
        $this->merchantInvoiceId   = $merchantInvoiceId;
        $this->referringMerchantId = $referringMerchantId;
    }

    /**
     * Get merchantId
     * @return string
     */
    public function merchantId(): ?string
    {
        return $this->merchantId;
    }

    /**
     * Get merchantPassword
     * @return string
     */
    public function merchantPassword(): string
    {
        return $this->merchantPassword;
    }

    /**
     * Get $merchantCustomerId
     * @return string
     */
    public function merchantCustomerId(): ?string
    {
        return $this->merchantCustomerId;
    }

    /**
     * Get $merchantInvoiceId
     * @return string
     */
    public function merchantInvoiceId(): ?string
    {
        return $this->merchantInvoiceId;
    }

    /**
     * @return string|null
     */
    public function referringMerchantId(): ?string
    {
        return $this->referringMerchantId;
    }

    /**
     * @return string
     */
    public function billerName(): string
    {
        return self::ROCKETGATE;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "merchantId"          => $this->merchantId(),
            "merchantCustomerId"  => $this->merchantCustomerId(),
            "merchantInvoiceId"   => $this->merchantInvoiceId(),
            "merchantPassword"    => $this->merchantPassword(),
            "referringMerchantId" => $this->referringMerchantId()
        ];
    }
}
