<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

class Member
{
    /**
     * @var string|null
     */
    public $firstName;

    /**
     * @var string|null
     */
    public $lastName;

    /**
     * @var string|null
     */
    public $userName;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $phone;

    /**
     * @var string|null
     */
    public $address;

    /**
     * @var string|null
     */
    public $zipCode;

    /**
     * @var string|null
     */
    public $city;

    /**
     * @var string|null
     */
    public $state;

    /**
     * @var string|null
     */
    public $country;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var string|null
     */
    public $memberId;

    /**
     * Member constructor.
     * @param null|string $firstName First name
     * @param null|string $lastName  Last name
     * @param null|string $userName  User Name
     * @param null|string $email     Email
     * @param null|string $phone     Phone
     * @param null|string $address   Address
     * @param null|string $zipCode   Zip code
     * @param null|string $city      City
     * @param null|string $state     State
     * @param null|string $country   Country
     * @param null|string $password  Password
     * @param null|string $memberId  Member Id
     */
    public function __construct(
        ?string $firstName,
        ?string $lastName,
        ?string $userName,
        ?string $email,
        ?string $phone,
        ?string $address,
        ?string $zipCode,
        ?string $city,
        ?string $state,
        ?string $country,
        ?string $password = null, // only needed for netbilling
        ?string $memberId = null // only needed for epoch sec rev
    ) {
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->userName  = $userName;
        $this->email     = $email;
        $this->phone     = $phone;
        $this->address   = $address;
        $this->zipCode   = $zipCode;
        $this->city      = $city;
        $this->state     = $state;
        $this->country   = $country;
        $this->password  = $password;
        $this->memberId  = $memberId;
    }

    /**
     * @return null|string
     */
    public function firstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return null|string
     */
    public function lastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return null|string
     */
    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * @return null|string
     */
    public function phone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return null|string
     */
    public function address(): ?string
    {
        return $this->address;
    }

    /**
     * @return null|string
     */
    public function zipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     * @return null|string
     */
    public function city(): ?string
    {
        return $this->city;
    }

    /**
     * @return null|string
     */
    public function state(): ?string
    {
        return $this->state;
    }

    /**
     * @return null|string
     */
    public function country(): ?string
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function userName(): ?string
    {
        return $this->userName;
    }

    /**
     * @return string|null
     */
    public function password(): ?string
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function memberId(): ?string
    {
        return $this->memberId;
    }

    /**
     * @return string|null
     */
    public function fullName(): ?string
    {
        return $this->firstName() . ' ' . $this->lastName();
    }
}
