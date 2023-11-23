<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;

class MerchantCustomerId
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Id constructor.
     *
     * @param string $value Uuid
     * @throws \Exception
     */
    private function __construct(string $value = null)
    {
        $this->value = $value;
    }

    /**
     * Create new Id from Uuid
     *
     * @param string|null $value Uuid
     * @return MerchantCustomerId
     * @throws \Exception
     */
    public static function create(string $value = null): self
    {
        if (empty($value)) {
            throw new InvalidMerchantInformationException('merchantCustomerId');
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
     * Compares two MerchantCustomerId
     *
     * @param MerchantCustomerId $new Id
     * @return bool
     */
    public function equals(MerchantCustomerId $new): bool
    {
        return $this->value() === $new->value();
    }
}
