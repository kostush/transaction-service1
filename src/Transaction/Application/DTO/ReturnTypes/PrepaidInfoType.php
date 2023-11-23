<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes;

class PrepaidInfoType
{
    public const AMOUNT_ATTRIBUTE   = 'amount';
    public const CURRENCY_ATTRIBUTE = 'currency';

    /** @var float|null */
    private $amount;

    /** @var string|null */
    private $currency;

    /**
     * PrepaidInfoType constructor.
     *
     * @param float|null  $amount   Balance Amount.
     * @param string|null $currency Balance Currency.
     */
    public function __construct(?float $amount, ?string $currency)
    {
        $this->amount   = $amount;
        $this->currency = $currency;
    }

    /**
     * @param float|null  $amount   Balance Amount.
     * @param string|null $currency Balance Currency.
     *
     * @return PrepaidInfoType
     */
    public static function create(?float $amount, ?string $currency): self
    {
        return new static($amount, $currency);
    }

    /**
     * @return float|null
     */
    public function amount(): ?float
    {
        return $this->amount;
    }

    /**
     * @return string|null
     */
    public function currency(): ?string
    {
        return $this->currency;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return (!empty($this->currency) && !empty($this->amount));
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
