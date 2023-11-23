<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;

class RocketGateUpdateRebillBillerFields extends RocketGateChargeSettings
{
    /**
     * RocketGateCancelRebillBillerFields constructor.
     * @param string      $merchantId         Merchant Id
     * @param string      $merchantPassword   Merchant password
     * @param string|null $merchantCustomerId Merchant customer id
     * @param string|null $merchantInvoiceId  Merchant invoice id
     * @param string|null $merchantAccount    Merchant account
     * @throws MissingMerchantInformationException
     * @throws Exception
     * @throws InvalidMerchantInformationException
     */
    public function __construct(
        ?string $merchantId,
        ?string $merchantPassword,
        ?string $merchantCustomerId,
        ?string $merchantInvoiceId,
        ?string $merchantAccount
    ) {
        $this->initMerchantCustomerId($merchantCustomerId);
        $this->initMerchantInvoiceId($merchantInvoiceId);

        parent::__construct(
            $merchantId,
            $merchantPassword,
            $this->merchantCustomerId,
            $this->merchantInvoiceId,
            $merchantAccount,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );
    }

    /**
     * @param null|string $merchantCustomerId Merchant customer Id
     * @throws MissingMerchantInformationException
     * @throws Exception
     * @return void
     */
    protected function initMerchantCustomerId(?string $merchantCustomerId): void
    {
        if (empty($merchantCustomerId)) {
            throw new MissingMerchantInformationException('merchantCustomerId');
        }

        parent::initMerchantCustomerId($merchantCustomerId);

        $this->merchantCustomerId = $merchantCustomerId;
    }

    /**
     * @param null|string $merchantInvoiceId Merchant customer Id
     * @throws MissingMerchantInformationException
     * @throws Exception
     * @return void
     */
    protected function initMerchantInvoiceId(?string $merchantInvoiceId): void
    {
        if (empty($merchantInvoiceId)) {
            throw new MissingMerchantInformationException('merchantInvoiceId');
        }

        parent::initMerchantInvoiceId($merchantInvoiceId);

        $this->merchantInvoiceId = $merchantInvoiceId;
    }
}
