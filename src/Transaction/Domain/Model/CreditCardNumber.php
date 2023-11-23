<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;

class CreditCardNumber implements ObfuscatedData
{
    const AMEX_CC_TYPE = 'amex';

    /**
     * @var string
     */
    public $cardNumber;

    /**
     * @var string
     */
    public $cardType;

    /**
     * @var bool
     */
    public $isValidNumber;

    /**
     * @var string
     */
    public $firstSix;

    /**
     * @var string
     */
    public $lastFour;

    /**
     * CreditCardNumber constructor.
     * @param string $cardNumber  Credit card number
     * @param array  $cardDetails Credit card details
     */
    private function __construct(
        string $cardNumber,
        array $cardDetails
    ) {
        $this->cardNumber    = $cardNumber;
        $this->cardType      = $cardDetails['type'];
        $this->isValidNumber = $cardDetails['valid'];
        $this->firstSix      = $cardDetails['firstSix'];
        $this->lastFour      = $cardDetails['lastFour'];
    }

    /**
     * Create new creditCardNumber object
     * @param string $cardNumber Card number
     * @return self
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws \ProBillerNG\Logger\Exception
     */
    public static function create(?string $cardNumber): self
    {
        $cardDetails = CreditCard::validCreditCard($cardNumber);

        // this is the case where we are trying to deserialize the
        // payment information from database
        if (self::isJson((string) $cardNumber) && $cardDetails['valid'] === false) {
            $decodedCardNumber = json_decode($cardNumber, true);

            return new self(
                $decodedCardNumber['cardNumber'],
                [
                    'type'     => $decodedCardNumber['cardType'],
                    'valid'    => $decodedCardNumber['isValidNumber'],
                    'firstSix' => $decodedCardNumber['firstSix'],
                    'lastFour' => $decodedCardNumber['lastFour'],
                ]
            );
        }

        if (!is_string($cardNumber) || empty($cardDetails['number'])) {
            throw new InvalidCreditCardNumberException(
                'number is not valid: '
                . 'First 6: '. substr($cardNumber, 0, 6)
                . ' and it contains ' . strlen($cardNumber) . ' chars'
                . ' and Luhn is '. CreditCardNumber::luhnCheck($cardNumber)
            );
        }

        $cardDetails['firstSix'] = self::initFirstSix($cardDetails['number']);
        $cardDetails['lastFour'] = self::initLastFour($cardDetails['number']);

        return new self($cardDetails['number'], $cardDetails);
    }

    /**
     * Init First six
     * @param string $cardNumber The credit card number
     * @return string
     */
    private static function initFirstSix($cardNumber): string
    {
        return substr($cardNumber, 0, 6);
    }

    /**
     * Init Last four
     * @param string $cardNumber The credit card number
     * @return string
     */
    private static function initLastFour($cardNumber): string
    {
        return substr($cardNumber, -4);
    }

    /**
     * !!Use this method only for re-generation of VO based on DB values!!
     * @param array $cardDetails Card details as read from database
     * @return CreditCardNumber
     */
    public static function createObfuscated(array $cardDetails): self
    {
        return new self(self::OBFUSCATED_STRING, $cardDetails);
    }

    /**
     * Get cardNumber
     * @return string
     */
    public function cardNumber(): string
    {
        return $this->cardNumber;
    }

    /**
     * Get cardType e.g.: visa, mastercard, etc.
     * @return string
     */
    public function cardType(): string
    {
        return $this->cardType;
    }

    /**
     * @return bool
     */
    public function isValidNumber(): bool
    {
        return $this->isValidNumber;
    }
    /**
     * @return string
     */
    public function firstSix(): string
    {
        return $this->firstSix;
    }

    /**
     * @return string
     */
    public function lastFour(): string
    {
        return $this->lastFour;
    }


    /**
     * @param CreditCardNumber $creditCardNumber CreditCardNumber object
     * @return bool
     */
    public function equals(CreditCardNumber $creditCardNumber): bool
    {
        return (
            ($this->cardNumber === $creditCardNumber->cardNumber())
            && ($this->cardType === $creditCardNumber->cardType())
            && ($this->isValidNumber === $creditCardNumber->isValidNumber())
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->cardNumber();
    }

    /**
     * @param string $number Credit Card Number
     * @return string
     */
    protected static function luhnCheck($number): string
    {
        $checksum     = 0;
        $lengthNumber = strlen($number);

        for ($i = (2 - ($lengthNumber % 2)); $i <= $lengthNumber; $i += 2) {
            $checksum += (int) ($number{$i - 1});
        }


        // Analyze odd digits in even length strings or even digits in odd length strings.
        for ($i = ($lengthNumber % 2) + 1; $i < $lengthNumber; $i += 2) {
            $digit = (int) ($number{$i - 1}) * 2;
            if ($digit < 10) {
                $checksum += $digit;
            } else {
                $checksum += ($digit - 9);
            }
        }

        if (($checksum % 10) == 0) {
            return 'valid';
        } else {
            return 'invalid';
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $creditCardNumberArray                  = get_object_vars($this);
        $creditCardNumberArray['cardNumber']    = self::OBFUSCATED_STRING;
        $creditCardNumberArray['isValidNumber'] = (int) $this->isValidNumber;

        return $creditCardNumberArray;
    }

    /**
     * @param $string String param.
     * @return bool
     */
    private static function isJson($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) ? true : false;
    }
}
