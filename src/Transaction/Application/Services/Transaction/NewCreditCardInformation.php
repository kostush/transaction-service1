<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Validators;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingCreditCardInformationException;

class NewCreditCardInformation extends Information
{
    use Validators;

    /**
     * @var string
     */
    public $number;

    /**
     * @var int
     */
    public $expirationMonth;

    /**
     * @var int
     */
    public $expirationYear;

    /**
     * @var string
     */
    public $cvv;

    /**
     * @var Member|null
     */
    public $member;

    /**
     * Information constructor.
     * @param null|string $number          Credit card number
     * @param string|null $expirationMonth Expiration month
     * @param int         $expirationYear  Expiration year
     * @param null|string $cvv             CVV
     * @param null|Member $member          Member
     * @throws InvalidCreditCardExpirationDateException
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        ?string $number,
        ?string $expirationMonth,
        $expirationYear,
        ?string $cvv,
        ?Member $member
    ) {
        $this->initNumber($number);
        $this->member = $member;
        $this->initCvv($cvv);
        $this->initExpirationMonth($expirationMonth);
        $this->initExpirationYear($expirationYear);
    }

    /**
     * @param null|string $number Credit card number
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    private function initNumber(?string $number): void
    {
        if (empty($number)) {
            throw new MissingCreditCardInformationException('number');
        }
        $this->number = $number;
    }

    /**
     * @param null|string $cvv Cvv
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    private function initCvv(?string $cvv): void
    {
        if (empty($cvv)) {
            throw new MissingCreditCardInformationException('cvv');
        }

        $this->cvv = $cvv;
    }

    /**
     * @param string $expirationMonth Expiration month
     * @return void
     * @throws InvalidCreditCardExpirationDateException
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initExpirationMonth(?string $expirationMonth): void
    {
        if ($expirationMonth === null) {
            throw new MissingCreditCardInformationException('expirationMonth');
        } elseif (!$this->isValidCardMonth($expirationMonth)) {
            throw new InvalidCreditCardExpirationDateException('expirationMonth');
        }
        $this->expirationMonth = (int) $expirationMonth;
    }

    /**
     * @param string $expirationYear Expiration year
     * @return void
     * @throws InvalidCreditCardExpirationDateException
     * @throws MissingCreditCardInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initExpirationYear($expirationYear): void
    {
        if (empty($expirationYear)) {
            throw new MissingCreditCardInformationException('expirationYear');
        } elseif (!$this->isValidInteger($expirationYear)) {
            throw new InvalidCreditCardExpirationDateException('expirationYear');
        }
        $this->expirationYear = (int) $expirationYear;
    }

    /**
     * @return string|null
     */
    public function number(): ?string
    {
        return $this->number;
    }

    /**
     * @return int|null
     */
    public function expirationMonth(): ?int
    {
        return $this->expirationMonth;
    }

    /**
     * @return int
     */
    public function expirationYear(): ?int
    {
        return $this->expirationYear;
    }

    /**
     * @return string
     */
    public function cvv(): ?string
    {
        return $this->cvv;
    }

    /**
     * @return Member|null
     */
    public function member(): ?Member
    {
        return $this->member;
    }
}
