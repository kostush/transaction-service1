<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

abstract class BillingAddress
{
    /**
     * @var $ownerAddress string
     */
    public $ownerAddress;

    /**
     * @var $ownerAddress string
     */
    public $ownerCity;

    /**
     * @var $ownerAddress string
     */
    public $ownerCountry;

    /**
     * @var $ownerAddress string
     */
    public $ownerState;

    /**
     * @var $ownerAddress string
     */
    public $ownerZip;

    /**
     * @var $ownerAddress string
     */
    public $ownerPhoneNo;

    /**
     * BillingAddress constructor.
     *
     * @param string | null $ownerAddress owner address
     * @param string | null $ownerCity    owner city
     * @param string | null $ownerCountry owner country
     * @param string | null $ownerState   owner state
     * @param string | null $ownerZip     owner zip code
     * @param string | null $ownerPhoneNo owner phone no
     *
     */
    private function __construct(
        ?string $ownerAddress,
        ?string $ownerCity,
        ?string $ownerCountry,
        ?string $ownerState,
        ?string $ownerZip,
        ?string $ownerPhoneNo
    ) {
        $this->ownerAddress = $ownerAddress;
        $this->ownerCity    = $ownerCity;
        $this->ownerCountry = $ownerCountry;
        $this->ownerState   = $ownerState;
        $this->ownerZip     = $ownerZip;
        $this->ownerPhoneNo = $ownerPhoneNo;
    }

    /**
     * @param string | null $ownerAddress owner address
     * @param string | null $ownerCity    owner city
     * @param string | null $ownerCountry owner country
     * @param string | null $ownerState   owner state
     * @param string | null $ownerZip     owner zip code
     * @param string | null $ownerPhoneNo owner phone no
     *
     * @return BillingAddress
     */
    public static function create(
        ?string $ownerAddress,
        ?string $ownerCity,
        ?string $ownerCountry,
        ?string $ownerState,
        ?string $ownerZip,
        ?string $ownerPhoneNo
    ): self {
        return new static(
            $ownerAddress,
            $ownerCity,
            $ownerCountry,
            $ownerState,
            $ownerZip,
            $ownerPhoneNo
        );
    }

    /**
     * @return string
     */
    public function ownerAddress(): ?string
    {
        return $this->ownerAddress;
    }

    /**
     * Get ownerCity
     * @return string|null
     */
    public function ownerCity(): ?string
    {
        return $this->ownerCity;
    }

    /**
     * Get ownerCountry
     * @return string|null
     */
    public function ownerCountry(): ?string
    {
        return $this->ownerCountry;
    }

    /**
     * Get ownerState
     * @return string|null
     */
    public function ownerState(): ?string
    {
        return $this->ownerState;
    }

    /**
     * Get ownerZip
     * @return string|null
     */
    public function ownerZip(): ?string
    {
        return $this->ownerZip;
    }

    /**
     * Get ownerPhoneNo
     * @return string|null
     */
    public function ownerPhoneNo(): ?string
    {
        return $this->ownerPhoneNo;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param BillingAddress $billingAddress BillingAddress object
     *
     * @return bool
     */
    abstract public function equals(BillingAddress $billingAddress): bool;
}
