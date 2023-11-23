<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class LookupThreeDsTwoCommand extends Command
{
    /**
     * @var string
     */
    private $deviceFingerprintingId;

    /**
     * @var string
     */
    private $previousTransactionId;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var
     */
    private $isNSFSupported;

    /**
     * LookupThreeDsTwoCommand constructor.
     *
     * @param string  $deviceFingerprintingId Device fingerprint id
     * @param string  $previousTransactionId  Previous transaction id
     * @param string  $redirectUrl            Redirect url
     * @param Payment $payment                Payment
     * @param bool    $isNSFSupported
     *
     * @throws MissingChargeInformationException
     */
    public function __construct(
        string $deviceFingerprintingId,
        string $previousTransactionId,
        string $redirectUrl,
        Payment $payment,
        $isNSFSupported = false
    ) {
        $this->initDeviceFingerprintingId($deviceFingerprintingId);
        $this->initPreviousTransactionId($previousTransactionId);
        $this->initRedirectUrl($redirectUrl);
        $this->payment        = $payment;
        $this->isNSFSupported = $isNSFSupported;
    }

    /**
     * @return string
     */
    public function deviceFingerprintingId(): string
    {
        return $this->deviceFingerprintingId;
    }

    /**
     * @return string
     */
    public function previousTransactionId(): string
    {
        return $this->previousTransactionId;
    }

    /**
     * @return string
     */
    public function redirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * @return Payment
     */
    public function payment(): Payment
    {
        return $this->payment;
    }

    /**
     * @return bool
     */
    public function isNSFSupported(): bool
    {
        return $this->isNSFSupported;
    }

    /**
     * @param string $deviceFingerprintingId Device fingerprint id
     *
     * @return void
     * @throws MissingChargeInformationException
     */
    private function initDeviceFingerprintingId(string $deviceFingerprintingId): void
    {
        if (empty($deviceFingerprintingId)) {
            throw new MissingChargeInformationException('deviceFingerprintingId');
        }

        $this->deviceFingerprintingId = $deviceFingerprintingId;
    }

    /**
     * @param string $previousTransactionId Previous transaction id
     *
     * @return void
     * @throws MissingChargeInformationException
     */
    private function initPreviousTransactionId(string $previousTransactionId): void
    {
        if (empty($previousTransactionId)) {
            throw new MissingChargeInformationException('previousTransactionId');
        }

        $this->previousTransactionId = $previousTransactionId;
    }

    /**
     * @param string $redirectUrl Redirect URL
     *
     * @return void
     * @throws MissingChargeInformationException
     */
    private function initRedirectUrl(string $redirectUrl): void
    {
        if (empty($redirectUrl)) {
            throw new MissingChargeInformationException('deviceFingerprintingId');
        }

        $this->redirectUrl = $redirectUrl;
    }
}
