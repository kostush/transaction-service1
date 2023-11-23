<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Netbilling;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\Transaction\Payment;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use ProBillerNG\Transaction\Domain\Services\CreditCardCharge;

class PerformNetbillingUpdateRebillCommand extends Command
{
    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var string
     */
    protected $siteTag;

    /**
     * @var string
     */
    protected $accountId;

    /**
     * @var int
     */
    protected $initialDays;

    /**
     * @var string|null
     */
    protected $binRouting;

    /**
     * @var string
     */
    protected $merchantPassword;

    /**
     * @var array
     */
    protected $rebill;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string|null
     */
    protected $currency;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * PerformNetbillingUpdateRebillCommand constructor.
     * @param string      $transactionId    transaction id
     * @param string      $siteTag          site tag
     * @param string      $accountId        account id
     * @param string      $merchantPassword merchantPassword
     * @param mixed       $rebill           rebill information
     * @param mixed       $amount           amount
     * @param Payment     $payment          payment information
     * @param string|null $binRouting       routing code
     * @param string|null $currency         currency
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    public function __construct(
        string $transactionId,
        string $siteTag,
        string $accountId,
        string $merchantPassword,
        $rebill,
        $amount,
        Payment $payment,
        ?string $binRouting = null,
        ?string $currency = null
    ) {
        $this->transactionId    = $transactionId;
        $this->siteTag          = $siteTag;
        $this->accountId        = $accountId;
        $this->merchantPassword = $merchantPassword;
        $this->binRouting       = $binRouting;
        $this->currency         = $currency;

        $this->initRebill($rebill);
        $this->initialDays = $rebill['start'];
        $this->initAmount($amount);
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
    public function siteTag(): string
    {
        return $this->siteTag;
    }

    /**
     * @return string
     */
    public function accountId(): string
    {
        return $this->accountId;
    }

    /**
     * @return string
     */
    public function merchantPassword(): string
    {
        return $this->merchantPassword;
    }

    /**
     * @return int|null
     */
    public function initialDays(): ?int
    {
        return $this->initialDays;
    }

    /**
     * @return string|null
     */
    public function binRouting(): ?string
    {
        return $this->binRouting;
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
     * @return string|null
     */
    public function currency(): ?string
    {
        return $this->currency;
    }

    /**
     * @return Payment
     */
    public function payment(): Payment
    {
        return $this->payment;
    }

    /**
     * @return string
     */
    public function paymentType(): string
    {
        return (string) (
            $this->payment()->type()
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
     * @param Payment $payment Payment
     * @return void
     * @throws InvalidChargeInformationException
     * @throws Exception
     */
    private function initPayment(Payment $payment): void
    {
        if (!empty($this->amount)) {
            if (empty($payment)) {
                throw new InvalidChargeInformationException('payment information');
            }

            if ($payment->type() != PaymentType::CREDIT_CARD) {
                throw new InvalidChargeInformationException('type');
            }
        }

        $this->payment = $payment;
    }

    /**
     * @param mixed $amount Amount
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    private function initAmount($amount): void
    {
        if (!is_numeric($amount)) {
            throw new InvalidChargeInformationException('amount');
        }

        $this->amount = (float) $amount;
    }

    /**
     * @param mixed $rebill Rebill
     * @return void
     * @throws Exception
     * @throws InvalidChargeInformationException
     */
    private function initRebill($rebill): void
    {
        if (!is_array($rebill)) {
            throw new InvalidChargeInformationException('rebill');
        }
        if (!empty($rebill)) {
            if (!isset($rebill['amount']) || !is_numeric($rebill['amount']) || $rebill['amount'] < 0) {
                throw new InvalidChargeInformationException('rebill => amount');
            }
            if (!isset($rebill['start']) || !is_int($rebill['start'])) {
                throw new InvalidChargeInformationException('rebill => start');
            }
            if (!isset($rebill['frequency']) || !is_int($rebill['frequency'])) {
                throw new InvalidChargeInformationException('rebill => frequency');
            }

            //For non recurring
            if ($rebill['amount'] == 0 && $rebill['frequency'] != 0) {
                throw new InvalidChargeInformationException('rebill => amount, frequency');
            }
            if ($rebill['frequency'] == 0 && $rebill['amount'] != 0) {
                throw new InvalidChargeInformationException('rebill => frequency, amount');
            }
        }

        $this->rebill = $rebill;
    }
}
