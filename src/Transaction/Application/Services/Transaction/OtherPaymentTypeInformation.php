<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentMethodException;
use ProBillerNG\Transaction\Domain\Model\PaymentMethod;

class OtherPaymentTypeInformation extends Information
{
    /**
     * @var string
     */
    private $routingNumber;

    /**
     * @var string
     */
    private $accountNumber;

    /**
     * @var bool
     */
    private $savingAccount;

    /**
     * @var string;
     */
    private $socialSecurityLast4;

    /**
     * @var Member|null
     */
    public $member;
    /**
     * @var string|null
     */
    private $method;

    /**
     * OtherPaymentInformation constructor.
     * @param string      $routingNumber       Routing Number
     * @param string      $accountNumber       Account Number
     * @param bool        $savingAccount       Saving account
     * @param string      $socialSecurityLast4 Social security last four digits
     * @param Member|null $member              Member
     * @param string|null $method              Method
     * @throws InvalidPaymentMethodException
     */
    public function __construct(
        string $routingNumber,
        string $accountNumber,
        bool $savingAccount,
        string $socialSecurityLast4,
        ?Member $member,
        ?string $method
    ) {
        $this->routingNumber       = $routingNumber;
        $this->accountNumber       = $accountNumber;
        $this->savingAccount       = $savingAccount;
        $this->socialSecurityLast4 = $socialSecurityLast4;
        $this->member              = $member;
        $this->initMethod($method);
    }

    /**
     * @param string|null $method Method
     * @throws InvalidPaymentMethodException
     * @return void
     */
    private function initMethod(?string $method): void
    {
        if ($method !== PaymentMethod::CHECKS) {
            throw new InvalidPaymentMethodException($method);
        }

        $this->method = $method;
    }

    /**
     * @return string
     */
    public function routingNumber(): string
    {
        return $this->routingNumber;
    }

    /**
     * @return string
     */
    public function accountNumber(): string
    {
        return $this->accountNumber;
    }

    /**
     * @return bool
     */
    public function savingAccount(): bool
    {
        return $this->savingAccount;
    }

    /**
     * @return string
     */
    public function socialSecurityLast4(): string
    {
        return $this->socialSecurityLast4;
    }

    /**
     * @return string|null
     */
    public function method(): ?string
    {
        return $this->method;
    }

    /**
     * @return Member|null
     */
    public function member(): ?Member
    {
        return $this->member;
    }
}
