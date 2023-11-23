<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;

class NetbillingCardHash
{
    /**
     * @var string
     */
    protected $value;

    /**
     * NetbillingCardHash constructor.
     *
     * @param string $value Uuid
     * @throws Exception
     */
    private function __construct(string $value = null)
    {
        $this->value = $value;
    }

    /**
     * Create new NetbillingCardHash
     *
     * @param string|null $base64EncodedHash Card Hash
     * @return NetbillingCardHash
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function create(string $base64EncodedHash = null): self
    {
        $value = base64_decode($base64EncodedHash);
        if (!preg_match('/^CS\:\\d{1,12}\:\\d{4}$/', $value)) {
            throw new InvalidCreditCardInformationException("cardHash does not match CS:<trans_id>:<last 4>");
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
     * Compares two NetbillingCardHashes
     *
     * @param NetbillingCardHash $new Id
     * @return bool
     */
    public function equals(NetbillingCardHash $new): bool
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
