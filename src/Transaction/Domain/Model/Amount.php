<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Validators;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;

class Amount
{
    use Validators;

    /**
     * @var float
     */
    private $value;

    /**
     * Amount constructor.
     *
     * @param float|null $value Amount value
     *
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    private function __construct(?float $value)
    {
        $this->initAmount($value);
    }

    /**
     * @param float $value Amount
     *
     * @return Amount
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    public static function create(?float $value): self
    {
        return new static($value);
    }

    /**
     * @return float
     */
    public function value(): ?float
    {
        return $this->value;
    }

    /**
     * @param Amount $value Amount object
     *
     * @return bool
     */
    public function equals(Amount $value): bool
    {
        return $this->value() === $value->value();
    }

    /**
     * @param float $value Amount value
     *
     * @return void
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    private function initAmount(?float $value): void
    {
        if ($value < 0) {
            throw new InvalidChargeInformationException('amount');
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
