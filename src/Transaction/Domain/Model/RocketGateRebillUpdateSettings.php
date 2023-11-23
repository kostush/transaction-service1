<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class RocketGateRebillUpdateSettings extends RocketGateBillerSettings
{
    /**
     * JMS Serializer does not know how to read inherited properties
     * se we must overwrite the abstract properties
     * @var string
     */
    protected $merchantId;

    /**
     * JMS Serializer does not know how to read inherited properties
     * se we must overwrite the abstract properties
     * @var string
     */
    protected $merchantPassword;

    /**
     * JMS Serializer does not know how to read inherited properties
     * se we must overwrite the abstract properties
     * @var string
     */
    protected $merchantCustomerId;

    /**
     * JMS Serializer does not know how to read inherited properties
     * se we must overwrite the abstract properties
     * @var string
     */
    protected $merchantInvoiceId;

    /**
     * @var string|null
     */
    protected $merchantAccount;

    /**
     * RocketGateChargeSettings constructor.
     *
     * @param string      $merchantId         Merchant Id
     * @param string      $merchantPassword   Merchant Password
     * @param string      $merchantCustomerId Merchant Customer Id
     * @param string      $merchantInvoiceId  Merchant Invoice Id
     * @param string|null $merchantAccount    Merchant Account
     * @throws \Exception
     */
    private function __construct(
        string $merchantId,
        string $merchantPassword,
        string $merchantCustomerId,
        string $merchantInvoiceId,
        ?string $merchantAccount
    ) {
        $this->merchantId         = $merchantId;
        $this->merchantPassword   = $merchantPassword;
        $this->merchantCustomerId = $merchantCustomerId;
        $this->merchantInvoiceId  = $merchantInvoiceId;
        $this->merchantAccount    = $merchantAccount;
    }

    /**
     * Create RocketGateChargeSettings.
     *
     * @param string      $merchantId         Merchant Id
     * @param string      $merchantPassword   Merchant Password
     * @param string      $merchantCustomerId Merchant Customer Id
     * @param string      $merchantInvoiceId  Merchant Invoice Id
     * @param string|null $merchantAccount    Merchant Account
     * @throws \Exception
     * @return self
     */
    public static function create(
        string $merchantId,
        string $merchantPassword,
        string $merchantCustomerId,
        string $merchantInvoiceId,
        ?string $merchantAccount
    ): self {
        return new static(
            $merchantId,
            $merchantPassword,
            $merchantCustomerId,
            $merchantInvoiceId,
            $merchantAccount
        );
    }

    /**
     * @return string
     */
    public function merchantAccount(): ?string
    {
        return $this->merchantAccount;
    }

    /**
     * Compare two Rocketgate Charge Settings objects
     *
     * @param RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings Charge Settings
     *
     * @return bool
     */
    public function equals(RocketGateRebillUpdateSettings $rocketGateCancelRebillSettings): bool
    {
        return (
            $this->merchantId() === $rocketGateCancelRebillSettings->merchantId()
            && $this->merchantPassword() === $rocketGateCancelRebillSettings->merchantPassword()
            && $this->merchantCustomerId() === $rocketGateCancelRebillSettings->merchantCustomerId()
            && $this->merchantInvoiceId() === $rocketGateCancelRebillSettings->merchantInvoiceId()
        );
    }
}
