<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;

class Email
{
    /**
     * @var string
     */
    private $email;

    /**
     * Email constructor.
     * @param string|null $email Email
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function __construct(?string $email)
    {
        $this->email = $this->initEmail($email);
    }

    /**
     * @param string|null $email Email
     * @return Email
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function create(?string $email): self
    {
        return new static($email);
    }

    /**
     * @return string|null
     */
    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function __toString(): ?string
    {
        return $this->email;
    }

    /**
     * @param Email $email Email to compare
     * @return bool
     */
    public function equals(Email $email): bool
    {
        return $this->email() === $email->email();
    }

    /**
     * @param string|null $email Email
     * @return string|null
     * @throws InvalidCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initEmail(?string $email): ?string
    {
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
            throw new InvalidCreditCardInformationException('email');
        }

        return $email;
    }
}
