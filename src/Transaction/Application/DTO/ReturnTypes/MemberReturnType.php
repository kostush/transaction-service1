<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes;

use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch\EpochBillerInteractionsReturnType;
use ProBillerNG\Transaction\Domain\Model\CheckInformation;
use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;

class MemberReturnType
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $phoneNumber;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * In some instances, such as Epoch transactions, we only have one name field
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $zip;

    /**
     * @var string
     */
    private $country;

    /**
     * TransactionPayloadMember constructor.
     *
     * @param null|string $email       Email
     * @param null|string $phoneNumber PhoneNumber
     * @param null|string $firstName   FirstName
     * @param null|string $lastName    LastName
     * @param null|string $name        Member name
     * @param null|string $address     Address
     * @param null|string $city        City
     * @param null|string $state       State
     * @param null|string $zip         Zip
     * @param null|string $country     Country
     */
    private function __construct(
        ?string $email,
        ?string $phoneNumber,
        ?string $firstName,
        ?string $lastName,
        ?string $name,
        ?string $address,
        ?string $city,
        ?string $state,
        ?string $zip,
        ?string $country
    ) {
        $this->email       = $email;
        $this->phoneNumber = $phoneNumber;
        $this->firstName   = $firstName;
        $this->lastName    = $lastName;
        $this->name        = $name;
        $this->address     = $address;
        $this->city        = $city;
        $this->state       = $state;
        $this->zip         = $zip;
        $this->country     = $country;
    }

    /**
     * @param EpochBillerInteractionsReturnType $billerInteraction The Epoch biller interactions object
     *
     * @return MemberReturnType
     */
    public static function createFromBillerInteraction(
        EpochBillerInteractionsReturnType $billerInteraction
    ): MemberReturnType {
        return new static(
            $billerInteraction->email(),
            null,
            null,
            null,
            $billerInteraction->name(),
            null,
            null,
            null,
            $billerInteraction->zip(),
            $billerInteraction->country()
        );
    }

    /**
     * @param CreditCardInformation $ccInfo The credit card object
     *
     * @return MemberReturnType
     */
    public static function createFromCreditCardInfo(CreditCardInformation $ccInfo)
    {
        $ownerEmail = ($ccInfo->creditCardOwner() !== null) ? $ccInfo->creditCardOwner()->ownerEmail()->email() : null;

        $ownerPhoneNo = ($ccInfo->creditCardBillingAddress() !== null) ? (
        $ccInfo->creditCardBillingAddress()->ownerPhoneNo()
        ) : null;

        $ownerFirstname = ($ccInfo->creditCardOwner() !== null) ?
            $ccInfo->creditCardOwner()->ownerFirstName() : null;

        $ownerLastName = ($ccInfo->creditCardOwner() !== null) ?
            $ccInfo->creditCardOwner()->ownerLastName() : null;

        $ownerAddress = ($ccInfo->creditCardBillingAddress() !== null) ?
            $ccInfo->creditCardBillingAddress()->ownerAddress() : null;

        $ownerCity = ($ccInfo->creditCardBillingAddress() !== null) ?
            $ccInfo->creditCardBillingAddress()->ownerCity() : null;

        $ownerState = ($ccInfo->creditCardBillingAddress() !== null) ?
            $ccInfo->creditCardBillingAddress()->ownerState() : null;

        $ownerZip = ($ccInfo->creditCardBillingAddress() !== null) ?
            $ccInfo->creditCardBillingAddress()->ownerZip() : null;

        $ownerCountry = ($ccInfo->creditCardBillingAddress() !== null) ?
            $ccInfo->creditCardBillingAddress()->ownerCountry() : null;

        return new static(
            $ownerEmail,
            $ownerPhoneNo,
            $ownerFirstname,
            $ownerLastName,
            null,
            $ownerAddress,
            $ownerCity,
            $ownerState,
            $ownerZip,
            $ownerCountry
        );
    }

    /**
     * @param CheckInformation $checkInfo Check Info.
     *
     * @return MemberReturnType
     */
    public static function createFromCheckInfo(CheckInformation $checkInfo): self
    {
        $ownerEmail = ($checkInfo->accountOwner() !== null) ? $checkInfo->accountOwner()->ownerEmail()->email() : null;

        $ownerPhoneNo = ($checkInfo->customerBillingAddress() !== null) ? (
        $checkInfo->customerBillingAddress()->ownerPhoneNo()
        ) : null;

        $ownerFirstname = ($checkInfo->accountOwner() !== null) ?
            $checkInfo->accountOwner()->ownerFirstName() : null;

        $ownerLastName = ($checkInfo->accountOwner() !== null) ?
            $checkInfo->accountOwner()->ownerLastName() : null;

        $ownerAddress = ($checkInfo->customerBillingAddress() !== null) ?
            $checkInfo->customerBillingAddress()->ownerAddress() : null;

        $ownerCity = ($checkInfo->customerBillingAddress() !== null) ?
            $checkInfo->customerBillingAddress()->ownerCity() : null;

        $ownerState = ($checkInfo->customerBillingAddress() !== null) ?
            $checkInfo->customerBillingAddress()->ownerState() : null;

        $ownerZip = ($checkInfo->customerBillingAddress() !== null) ?
            $checkInfo->customerBillingAddress()->ownerZip() : null;

        $ownerCountry = ($checkInfo->customerBillingAddress() !== null) ?
            $checkInfo->customerBillingAddress()->ownerCountry() : null;

        return new static(
            $ownerEmail,
            $ownerPhoneNo,
            $ownerFirstname,
            $ownerLastName,
            null,
            $ownerAddress,
            $ownerCity,
            $ownerState,
            $ownerZip,
            $ownerCountry
        );
    }

    /**
     * @param array $billerInteraction Biller Interaction
     *
     * @return MemberReturnType
     */
    public static function createMemberInfoFromBillerInteraction(array $billerInteraction): MemberReturnType
    {
        return new static(
            $billerInteraction['email'] ?? null,
            $billerInteraction['phone'] ?? null,
            $billerInteraction['firstName'] ?? null,
            $billerInteraction['lastName'] ?? null,
            $billerInteraction['username'] ?? null,
            $billerInteraction['address'] ?? null,
            $billerInteraction['cty'] ?? null,
            $billerInteraction['state'] ?? null,
            $billerInteraction['zipCode'] ?? null,
            $billerInteraction['country'] ?? null
        );
    }
}
