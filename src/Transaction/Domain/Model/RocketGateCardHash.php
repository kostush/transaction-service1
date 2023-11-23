<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;

class RocketGateCardHash
{

    /**
     * @var string
     */
    protected $value;

    /**
     * RocketGateCardHash constructor.
     *
     * @param string $value Uuid
     * @throws \Exception
     */
    private function __construct(string $value = null)
    {
        $this->value = $value;
    }

    /**
     * Create new RocketGateCardHash
     *
     * @param string|null $value Uuid
     * @return RocketGateCardHash
     * @throws \Exception
     */
    public static function create(string $value = null): self
    {
        if (empty($value) || strlen(trim($value)) !== 44) {
            throw new InvalidCreditCardInformationException('cardHash');
        }
        return new static($value);
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Compares two RocketGateCardHashes
     *
     * @param RocketGateCardHash $new Id
     * @return bool
     */
    public function equals(RocketGateCardHash $new): bool
    {
        return $this->value() === $new->value();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}
