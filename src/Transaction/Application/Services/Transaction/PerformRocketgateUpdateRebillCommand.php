<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use ProBillerNG\Transaction\Domain\Services\CreditCardCharge;

class PerformRocketgateUpdateRebillCommand extends Command
{
    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $merchantPassword;

    /**
     * @var string
     */
    protected $merchantCustomerId;

    /**
     * @var string
     */
    protected $merchantInvoiceId;

    /**
     * @var string|null
     */
    protected $merchantAccount;

    /**
     * @var array
     */
    protected $rebill;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var array
     */
    protected $payment;

    /**
     * PerformRocketgateUpdateRebillCommand constructor.
     * @param string      $transactionId      Transaction id
     * @param string      $merchantId         Merchant Id
     * @param string      $merchantPassword   Merchant Password
     * @param string      $merchantCustomerId Merchant Customer Id
     * @param string      $merchantInvoiceId  Merchant Invoice Id
     * @param string|null $merchantAccount    Merchant Account
     * @param mixed       $rebill             Rebill
     * @param mixed       $amount             Amount
     * @param string      $currency           Currency
     * @param array       $payment            Payment
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        string $transactionId,
        string $merchantId,
        string $merchantPassword,
        string $merchantCustomerId,
        string $merchantInvoiceId,
        ?string $merchantAccount,
        $rebill,
        $amount,
        string $currency,
        array $payment
    ) {
        $this->transactionId      = $transactionId;
        $this->merchantId         = $merchantId;
        $this->merchantPassword   = $merchantPassword;
        $this->merchantCustomerId = $merchantCustomerId;
        $this->merchantInvoiceId  = $merchantInvoiceId;
        $this->merchantAccount    = $merchantAccount;
        $this->initRebill($rebill);
        $this->initAmount($amount);
        $this->initCurrency($currency);
        $this->initPayment($payment);
    }

    /**
     * @return string
     */
    public function transactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function merchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function merchantPassword(): string
    {
        return $this->merchantPassword;
    }

    /**
     * @return string
     */
    public function merchantCustomerId(): string
    {
        return $this->merchantCustomerId;
    }

    /**
     * @return string
     */
    public function merchantInvoiceId(): string
    {
        return $this->merchantInvoiceId;
    }

    /**
     * @return string|null
     */
    public function merchantAccount(): ?string
    {
        return $this->merchantAccount;
    }

    /**
     * @return array
     */
    public function rebill(): array
    {
        return $this->rebill;
    }

    /**
     * @return float
     */
    public function amount(): float
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * @return array
     */
    public function payment(): array
    {
        return $this->payment;
    }

    /**
     * @return string
     */
    public function paymentType(): string
    {
        return (string) (
            $this->payment()['method'] ?? ''
        );
    }

    /**
     * @return string
     */
    public function cardHash(): string
    {
        return (string) (
            $this->payment()['information']['cardHash'] ?? ''
        );
    }

    /**
     * @return string
     */
    public function ccNumber(): string
    {
        return (string) (
            $this->payment()['information']['number'] ?? ''
        );
    }

    /**
     * @return int
     */
    public function cardExpirationMonth(): ?int
    {
        return (int) (
            $this->payment()['information']['expirationMonth'] ?? null
        );
    }

    /**
     * @return int
     */
    public function cardExpirationYear(): ?int
    {
        return (int) (
            $this->payment()['information']['expirationYear'] ?? null
        );
    }

    /**
     * @return string
     */
    public function cvv(): string
    {
        return (string) (
            $this->payment()['information']['cvv'] ?? ''
        );
    }

    /**
     * @return float
     */
    public function rebillAmount(): ?float
    {
        return (float) (
            $this->rebill()['amount'] ?? null
        );
    }

    /**
     * @return int
     */
    public function rebillStart(): ?int
    {
        return (int) (
            $this->rebill()['start'] ?? null
        );
    }

    /**
     * @return int
     */
    public function rebillFrequency(): ?int
    {
        return (int) (
            $this->rebill()['frequency'] ?? null
        );
    }

    /**
     * @param string $currency Currency
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initCurrency(string $currency)
    {
        if (!empty($this->amount) && empty($currency)) {
            throw new InvalidChargeInformationException('currency');
        }
        $this->currency = $currency;
    }

    /**
     * @param array $payment Payment
     * @return void
     * @throws InvalidChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initPayment(array $payment)
    {
        if (!empty($this->amount)) {
            if (empty($payment)) {
                throw new InvalidChargeInformationException('payment information');
            }
            if (empty($payment['method']) || $payment['method'] != PaymentType::CREDIT_CARD) {
                throw new InvalidChargeInformationException('method');
            }
        }

        $this->payment = $payment;
    }

    /**
     * @param mixed $amount Amount
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws InvalidChargeInformationException
     */
    private function initAmount($amount)
    {
        if (!is_null($amount) && !is_numeric($amount)) {
            throw new InvalidChargeInformationException('amount');
        }

        $this->amount = (float) $amount;
    }

    /**
     * @param mixed $rebill Rebill
     * @return void
     * @throws \ProBillerNG\Logger\Exception
     * @throws InvalidChargeInformationException
     */
    private function initRebill($rebill)
    {
        if (!is_array($rebill)) {
            throw new InvalidChargeInformationException('rebill');
        }
        if (!empty($rebill)) {
            if (!is_numeric($rebill['amount']) || $rebill['amount'] <= 0) {
                throw new InvalidChargeInformationException('rebill => amount');
            }
            if (!is_int($rebill['start'])) {
                throw new InvalidChargeInformationException('rebill => start');
            }
            if (!is_int($rebill['frequency'])) {
                throw new InvalidChargeInformationException('rebill => frequency');
            }
        }

        $this->rebill = $rebill;
    }
}
