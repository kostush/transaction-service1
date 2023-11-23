<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso;

class QyssoMemberReturnType
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
     * In some instances, such as Qysso transactions, we only have one name field
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
    private $zip;

    /**
     * @var string
     */
    private $country;

    /**
     * TransactionPayloadMember constructor.
     * @param null|string $email       Email
     * @param null|string $phoneNumber PhoneNumber
     * @param null|string $name        Member name
     * @param null|string $address     Address
     * @param null|string $city        City
     * @param null|string $zip         Zip
     * @param null|string $country     Country
     */
    private function __construct(
        ?string $email,
        ?string $phoneNumber,
        ?string $name,
        ?string $address,
        ?string $city,
        ?string $zip,
        ?string $country
    ) {
        $this->email       = $email;
        $this->phoneNumber = $phoneNumber;
        $this->name        = $name;
        $this->address     = $address;
        $this->city        = $city;
        $this->zip         = $zip;
        $this->country     = $country;
    }

    /**
     * @param array $billerInteraction Biller Interaction
     * @return QyssoMemberReturnType
     */
    public static function createMemberInfoFromBillerInteraction(array $billerInteraction): QyssoMemberReturnType
    {
        return new static(
            $billerInteraction['Email'] ?? null,
            $billerInteraction['PhoneNumber'] ?? null,
            $billerInteraction['Member'] ?? null,
            $billerInteraction['BillingAddress1'] ?? null,
            $billerInteraction['BillingCity'] ?? null,
            $billerInteraction['BillingZipCode'] ?? null,
            $billerInteraction['BillingCountry'] ?? null
        );
    }
}
