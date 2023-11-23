<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use Inacho\CreditCard;
use JMS\Serializer\SerializerBuilder;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Validators;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;

class CreditCardInformation extends PaymentInformation implements PaymentInformationObfuscated
{
    use Validators;

    /**
     * @var bool
     */
    private $cvv2Check;

    /**
     * @var CreditCardNumber
     */
    private $creditCardNumber;

    /**
     * @var CreditCardOwner
     */
    private $creditCardOwner;

    /**
     * @var CreditCardBillingAddress
     */
    private $creditCardBillingAddress;

    /**
     * @var string
     */
    private $cvv;

    /**
     * @var int
     */
    private $expirationMonth;

    /**
     * @var int
     */
    private $expirationYear;

    /**
     * CreditCardInformation constructor.
     * @param bool                          $cvv2Check                bool value
     * @param CreditCardNumber              $creditCardNumber         CreditCardNumber object
     * @param CreditCardOwner|null          $creditCardOwner          CreditCardOwner object
     * @param CreditCardBillingAddress|null $creditCardBillingAddress CreditCardBillingAddress object
     * @param string|null                   $cvv                      int value
     * @param int                           $expirationMonth          int value
     * @param int                           $expirationYear           int value
     * @param bool                          $shouldCheckDate          Should check if date is valid
     * @throws InvalidCreditCardExpirationDateException
     */
    private function __construct(
        bool $cvv2Check,
        CreditCardNumber $creditCardNumber,
        ?CreditCardOwner $creditCardOwner,
        ?CreditCardBillingAddress $creditCardBillingAddress,
        ?string $cvv,
        $expirationMonth,
        $expirationYear,
        bool $shouldCheckDate
    ) {
        $this->cvv2Check                = $cvv2Check;
        $this->creditCardNumber         = $creditCardNumber;
        $this->creditCardOwner          = $creditCardOwner;
        $this->creditCardBillingAddress = $creditCardBillingAddress;
        $this->cvv                      = $cvv;

        $this->updateCardDate(
            $expirationYear,
            $expirationMonth,
            $shouldCheckDate
        );
    }

    /**
     * Create CreditCardInformation.
     * @param bool                          $cvv2Check                bool value
     * @param CreditCardNumber              $creditCardNumber         CreditCardNumber object
     * @param CreditCardOwner|null          $creditCardOwner          CreditCardOwner object
     * @param CreditCardBillingAddress|null $creditCardBillingAddress CreditCardBillingAddress object
     * @param string|null                   $cvv                      int value
     * @param int                           $expirationMonth          int value
     * @param int                           $expirationYear           int value
     * @param bool                          $shouldCheckDate          Should check date
     * @return self
     * @throws Exception
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     */
    public static function create(
        bool $cvv2Check,
        CreditCardNumber $creditCardNumber,
        ?CreditCardOwner $creditCardOwner,
        ?CreditCardBillingAddress $creditCardBillingAddress,
        ?string $cvv,
        $expirationMonth,
        $expirationYear,
        bool $shouldCheckDate = true
    ): self {
        return new static(
            $cvv2Check,
            $creditCardNumber,
            $creditCardOwner,
            $creditCardBillingAddress,
            self::checkCvv($cvv, $creditCardNumber->cardType()),
            $expirationMonth,
            $expirationYear,
            $shouldCheckDate
        );
    }

    /**
     * Check cvv
     * @param string $cvv  new value
     * @param string $type new value
     * @return string
     * @throws InvalidCreditCardCvvException
     * @throws Exception
     */
    private static function checkCvv($cvv, $type)
    {
        // $cvv could be obfuscated when trying to deserialize
        if (CreditCard::validCvc($cvv, $type)
            || strpos($cvv, '*') !== false) {
            return $cvv;
        } else {
            throw new InvalidCreditCardCvvException('cvv');
        }
    }

    /**
     * If card date is a valid one update the expirationMonth and expirationYear
     * @param int  $year            year
     * @param int  $month           month
     * @param bool $shouldCheckDate Should check if date is valid
     * @return void
     * @throws InvalidCreditCardExpirationDateException
     */
    private function updateCardDate($year, $month, bool $shouldCheckDate): void
    {
        if ($shouldCheckDate && !CreditCard::validDate((string) $year, (string) $month)) {
            throw new InvalidCreditCardExpirationDateException('Invalid date!' . $year . '/' . $month);
        }

        $this->expirationYear  = $year;
        $this->expirationMonth = $month;
    }

    /**
     * Get cvv2Check
     * @return bool
     */
    public function cvv2Check(): bool
    {
        return $this->cvv2Check;
    }

    /**
     * Get creditCardNumber
     * @return CreditCardNumber
     */
    public function creditCardNumber(): CreditCardNumber
    {
        return $this->creditCardNumber;
    }

    /**
     * Get creditCardOwner
     * @return CreditCardOwner|null
     */
    public function creditCardOwner(): ?CreditCardOwner
    {
        return $this->creditCardOwner;
    }

    /**
     * Get creditCardBillingAddress
     * @return CreditCardBillingAddress|null
     */
    public function creditCardBillingAddress(): ?CreditCardBillingAddress
    {
        return $this->creditCardBillingAddress;
    }

    /**
     * Get cvv
     * @return string
     */
    public function cvv(): string
    {
        return $this->cvv;
    }

    /**
     * Get $expirationMonth
     * @return int
     */
    public function expirationMonth(): int
    {
        return $this->expirationMonth;
    }

    /**
     * Get $expirationYear
     * @return int
     */
    public function expirationYear(): int
    {
        return $this->expirationYear;
    }

    /**
     * Check if one instance of CreditCardInformation is equal to another
     * @param CreditCardInformation $creditCardInformation CreditCardInformation object
     * @return bool
     */
    public function equals(CreditCardInformation $creditCardInformation): bool
    {
        return (
            ($this->cvv2Check == $creditCardInformation->cvv2Check())
            && ($this->creditCardNumber()->equals($creditCardInformation->creditCardNumber()))
            && ($this->creditCardOwner()->equals($creditCardInformation->creditCardOwner()))
            && ($this->creditCardBillingAddress()->equals($creditCardInformation->creditCardBillingAddress()))
            && ($this->cvv() === $creditCardInformation->cvv())
            && ($this->expirationMonth() === $creditCardInformation->expirationMonth())
            && ($this->expirationYear() === $creditCardInformation->expirationYear())
        );
    }

    /**
     * @return CreditCardInformation
     * @throws InvalidCreditCardInformationException
     * @throws Exception
     */
    public function returnObfuscatedDataForPersistence(): self
    {
        return new static(
            $this->cvv2Check(),
            CreditCardNumber::createObfuscated(
                [
                    'type'     => $this->creditCardNumber->cardType(),
                    'valid'    => $this->creditCardNumber->isValidNumber(),
                    'firstSix' => $this->creditCardNumber->firstSix(),
                    'lastFour' => $this->creditCardNumber->lastFour()
                ]
            ),
            $this->creditCardOwner(),
            $this->creditCardBillingAddress(),
            self::OBFUSCATED_STRING,
            $this->expirationMonth(),
            $this->expirationYear(),
            true
        );
    }

    /**
     * @return array
     */
    public function detailedInformation(): array
    {
        return [
            'first_six' => $this->creditCardNumber->firstSix(),
            'last_four' => $this->creditCardNumber->lastFour(),
        ];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'cvv2Check'                => $this->cvv2Check(),
            'creditCardNumber'         => $this->creditCardNumber() !== null ? $this->creditCardNumber()->toArray() : null,
            'creditCardOwner'          => $this->creditCardOwner() !== null ? $this->creditCardOwner()->toArray() : null,
            'creditCardBillingAddress' => $this->creditCardBillingAddress() !== null ? $this->creditCardBillingAddress()->toArray() : null,
            'cvv'                      => self::OBFUSCATED_STRING,
            'expirationMonth'          => $this->expirationMonth(),
            'expirationYear'           => $this->expirationYear(),
        ];
    }
}
