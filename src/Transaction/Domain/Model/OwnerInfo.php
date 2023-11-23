<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

abstract class OwnerInfo
{
    /**
     * @var string
     */
    private $ownerFirstName;

    /**
     * @var string
     */
    private $ownerLastName;

    /**
     * @var Email
     */
    private $ownerEmail;

    /**
     * @var string
     */
    private $ownerUserName;

    /**
     * @var string
     */
    private $ownerPassword;

    /**
     * CreditCardOwner constructor.
     * @param string|null $ownerFirstName Owner FirstName
     * @param string|null $ownerLastName  Owner LastName
     * @param Email|null  $ownerEmail     Owner Email
     * @param string|null $ownerUserName  Owner UserName
     * @param string|null $ownerPassword  Owner Password
     */
    private function __construct(
        ?string $ownerFirstName,
        ?string $ownerLastName,
        ?Email $ownerEmail,
        ?string $ownerUserName,
        ?string $ownerPassword
    ) {
        $this->ownerFirstName = $ownerFirstName;
        $this->ownerLastName  = $ownerLastName;
        $this->ownerEmail     = $ownerEmail;
        $this->ownerUserName  = $ownerUserName;
        $this->ownerPassword  = $ownerPassword;
    }

    /**
     * @param string|null $ownerFirstName Owner FirstName
     * @param string|null $ownerLastName  Owner LastName
     * @param Email|null  $ownerEmail     Owner Email
     * @param string|null $ownerUserName  Owner UserName
     * @param string|null $ownerPassword  Owner Password
     *
     * @return OwnerInfo
     */
    public static function create(
        ?string $ownerFirstName,
        ?string $ownerLastName,
        ?Email $ownerEmail,
        ?string $ownerUserName = null,
        ?string $ownerPassword = null
    ): self {
        return new static(
            $ownerFirstName,
            $ownerLastName,
            $ownerEmail,
            $ownerUserName,
            $ownerPassword
        );
    }

    /**
     * @return string|null
     */
    public function ownerFirstName(): ?string
    {
        return $this->ownerFirstName;
    }

    /**
     * @return string|null
     */
    public function ownerLastName(): ?string
    {
        return $this->ownerLastName;
    }

    /**
     * @return Email|null
     */
    public function ownerEmail(): ?Email
    {
        return $this->ownerEmail;
    }

    /**
     * @return string|null
     */
    public function ownerUserName(): ?string
    {
        return $this->ownerUserName;
    }

    /**
     * @return string|null
     */
    public function ownerPassword(): ?string
    {
        return $this->ownerPassword;
    }

    /**
     * @param OwnerInfo $creditCardOwner Credit Card Owner
     *
     * @return bool
     */
    public function equals(OwnerInfo $creditCardOwner): bool
    {
        return (
            ($this->ownerFirstName() === $creditCardOwner->ownerFirstName())
            && ($this->ownerLastName() === $creditCardOwner->ownerLastName())
            && ($this->ownerEmail()->equals($creditCardOwner->ownerEmail()))
            && ($this->ownerUserName() === $creditCardOwner->ownerUserName())
            && ($this->ownerPassword() === $creditCardOwner->ownerPassword())
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ownerFirstName' => $this->ownerFirstName(),
            'ownerLastName'  => $this->ownerLastName(),
            'ownerEmail'     => ['email' => (string) $this->ownerEmail()],
            'ownerUserName'  => (string) $this->ownerUserName(),
            'ownerPassword'  => (string) $this->ownerPassword()
        ];
    }
}
