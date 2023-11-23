<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use JMS\Serializer\SerializerBuilder;
use ProBillerNG\Transaction\Application\Services\Transaction\Rebill as CommandRebill;

class ChargeInformation
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var Rebill
     */
    private $rebill;

    /**
     * @var Amount|null
     */
    private $amount;

    /**
     * ChargeInformation constructor.
     * @param Currency|null $currency Currency information
     * @param Amount        $amount   Amount
     * @param Rebill|null   $rebill   Rebill Data
     */
    private function __construct(
        ?Currency $currency,
        Amount $amount,
        ?Rebill $rebill = null
    ) {
        $this->currency = $currency;
        $this->rebill   = $rebill;
        $this->amount   = $amount;
    }

    /**
     * @param Amount        $amount   Amount
     * @param Currency|null $currency Currency
     * @param Rebill|null   $rebill   Rebill
     * @return ChargeInformation
     */
    public static function create(
        Amount $amount,
        ?Currency $currency,
        ?Rebill $rebill = null
    ): self {
        return new static(
            $currency,
            $amount,
            $rebill
        );
    }

    /**
     * @param Currency|null $currency Currency information
     * @param Amount        $amount   Amount
     * @param Rebill        $rebill   Rebill Data
     * @return ChargeInformation
     */
    public static function createWithRebill(
        ?Currency $currency,
        Amount $amount,
        Rebill $rebill
    ): self {
        return new static(
            $currency,
            $amount,
            $rebill
        );
    }

    /**
     * @param Currency $currency Currency information
     * @param Amount   $amount   Amount
     * @return ChargeInformation
     */
    public static function createSingleCharge(
        ?Currency $currency,
        Amount $amount
    ): self {
        return new static(
            $currency,
            $amount
        );
    }

    /**
     * @return Currency|null
     */
    public function currency(): ?Currency
    {
        return $this->currency;
    }

    /**
     * @return Rebill|null
     */
    public function rebill(): ?Rebill
    {
        return $this->rebill;
    }

    /**
     * @return Amount|null
     */
    public function amount(): ?Amount
    {
        return $this->amount;
    }

    /**
     * @param ChargeInformation $chargeInformation Charge information
     * @return bool
     */
    public function equals(ChargeInformation $chargeInformation): bool
    {
        if (($this->rebill() === null && $chargeInformation->rebill() !== null)
            || ($this->rebill() !== null && $chargeInformation->rebill() === null)
        ) {
            return false;
        }

        if ($this->rebill() === null && $chargeInformation->rebill() === null) {
            return (
                ($this->amount()->equals($chargeInformation->amount()))
                && ($this->currency()->equals($chargeInformation->currency()))
            );
        }

        return (
            ($this->amount()->equals($chargeInformation->amount()))
            && ($this->rebill()->equals($chargeInformation->rebill()))
            && ($this->currency()->equals($chargeInformation->currency()))
        );
    }

    /**
     * @param Charge $charge Charge
     * @return ChargeInformation
     */
    public static function createChargeInformationFromCharge(Charge $charge): ?ChargeInformation
    {
        if (empty($charge->rebill())) {
            return ChargeInformation::createSingleCharge(
                $charge->currency(),
                $charge->amount() ?? 0
            );
        }

        return ChargeInformation::createWithRebill(
            $charge->currency(),
            $charge->amount(),
            $charge->rebill()
        );
    }

    /**
     * @param float              $amount   Amount.
     * @param string             $currency Currency.
     * @param CommandRebill|null $rebill   Rebill.
     * @return ChargeInformation
     * @throws Exception\InvalidChargeInformationException
     * @throws Exception\MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function createFromCommand(float $amount, string $currency, ?CommandRebill $rebill): ChargeInformation
    {
        if (is_null($rebill)) {
            return ChargeInformation::createSingleCharge(
                Currency::create($currency),
                Amount::create($amount)
            );
        }

        return ChargeInformation::createWithRebill(
            Currency::create($currency),
            Amount::create($amount),
            Rebill::create(
                $rebill->frequency(),
                $rebill->start(),
                Amount::create($rebill->amount())
            )
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'amount'   => [
                'value' => $this->amount()->value()
            ],
            'currency' => [
                'code' => (string) $this->currency()
            ],
            'rebill'   => $this->rebill() !== null ? $this->rebill()->toArray() : null,
        ];
    }
}
