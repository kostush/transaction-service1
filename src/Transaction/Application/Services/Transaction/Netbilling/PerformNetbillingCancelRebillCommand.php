<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction\Netbilling;

use ProBillerNG\Transaction\Application\Services\Command;

class PerformNetbillingCancelRebillCommand extends Command
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
     * @var string
     */
    protected $merchantPassword;

    /**
     * PerformNetbillingCancelRebillCommand constructor.
     * @param string $transactionId
     * @param string $siteTag
     * @param string $accountId
     * @param string $merchantPassword
     */
    public function __construct(
        string $transactionId,
        string $siteTag,
        string $accountId,
        string $merchantPassword
    ) {
        $this->transactionId    = $transactionId;
        $this->siteTag          = $siteTag;
        $this->accountId        = $accountId;
        $this->merchantPassword = $merchantPassword;
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
}