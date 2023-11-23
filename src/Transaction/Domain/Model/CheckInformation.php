<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use JMS\Serializer\SerializerBuilder;
use ProBillerNG\Transaction\Application\Services\Validators;

class CheckInformation extends PaymentInformation implements PaymentInformationObfuscated
{
    use Validators;

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
     * @var string
     */
    private $socialSecurityLast4;

    /**
     * @var AccountOwner|null
     */
    private $accountOwner;

    /**
     * @var CustomerBillingAddress|null
     */
    private $customerBillingAddress;

    /**
     * CheckInformation constructor.
     *
     * @param string              $routingNumber          routing Number
     * @param string              $accountNumber          account number
     * @param bool                $savingAccount          saving account
     * @param string              $socialSecurityLast4    social last 4
     * @param OwnerInfo|null      $accountOwner           Account Owner
     * @param BillingAddress|null $customerBillingAddress Customer Billing Address
     */
    public function __construct(
        string $routingNumber,
        string $accountNumber,
        bool $savingAccount,
        string $socialSecurityLast4,
        ?OwnerInfo $accountOwner,
        ?BillingAddress $customerBillingAddress
    ) {
        $this->routingNumber          = $routingNumber;
        $this->accountNumber          = $accountNumber;
        $this->savingAccount          = $savingAccount;
        $this->socialSecurityLast4    = $socialSecurityLast4;
        $this->accountOwner           = $accountOwner;
        $this->customerBillingAddress = $customerBillingAddress;
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
     * @return AccountOwner|null
     */
    public function accountOwner(): ?AccountOwner
    {
        return $this->accountOwner;
    }

    /**
     * @return CustomerBillingAddress|null
     */
    public function customerBillingAddress(): ?CustomerBillingAddress
    {
        return $this->customerBillingAddress;
    }

    /**
     * @return array
     */
    public function detailedInformation(): array
    {
        return [
            "routingNumber"       => $this->routingNumber(),
            "accountNumber"       => $this->accountNumber(),
            "savingAccount"       => $this->savingAccount(),
            "socialSecurityLast4" => $this->socialSecurityLast4()
        ];
    }

    /**
     * @return CheckInformation
     */
    public function returnObfuscatedDataForPersistence(): self
    {
        return new static(
            self::OBFUSCATED_STRING,
            self::OBFUSCATED_STRING,
            $this->savingAccount(),
            self::OBFUSCATED_STRING,
            $this->accountOwner(),
            $this->customerBillingAddress()
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'routingNumber'          => self::OBFUSCATED_STRING,
            'accountNumber'          => self::OBFUSCATED_STRING,
            'savingAccount'          => $this->savingAccount(),
            'socialSecurityLast4'    => self::OBFUSCATED_STRING,
            'accountOwner'           => $this->accountOwner() !== null ? $this->accountOwner()->toArray() : null,
            'customerBillingAddress' => $this->customerBillingAddress() !== null ? $this->customerBillingAddress()->toArray() : null
        ];
    }
}
