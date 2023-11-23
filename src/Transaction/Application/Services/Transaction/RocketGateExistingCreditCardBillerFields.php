<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;

class RocketGateExistingCreditCardBillerFields extends RocketGateChargeSettings
{
    /**
     * RocketGateExistingCreditCardBillerFields constructor.
     *
     * @param string|null $merchantId          Merchant id
     * @param string|null $merchantPassword    Merchant password
     * @param string|null $merchantCustomerId  Merchant customer id
     * @param string|null $merchantInvoiceId   Merchant invoice id
     * @param string|null $merchantAccount     Merchant account
     * @param string|null $merchantSiteId      Merchant site id
     * @param string|null $merchantProductId   Merchant product id
     * @param string|null $merchantDescriptor  Merchant descriptor
     * @param string|null $ipAddress           Ip address
     * @param string|null $referringMerchantId Referring merchant id
     * @param string|null $sharedSecret        Shared secret
     * @param bool|null   $simplified3DS       Simplified 3DS
     * @throws Exception
     * @throws InvalidMerchantInformationException
     * @throws MissingMerchantInformationException
     */
    public function __construct(
        ?string $merchantId,
        ?string $merchantPassword,
        ?string $merchantCustomerId,
        ?string $merchantInvoiceId,
        ?string $merchantAccount,
        ?string $merchantSiteId,
        ?string $merchantProductId,
        ?string $merchantDescriptor,
        ?string $ipAddress,
        ?string $referringMerchantId,
        ?string $sharedSecret,
        ?bool $simplified3DS
    ) {
        $this->validateMerchantCustomerId($merchantCustomerId);

        parent::__construct(
            $merchantId,
            $merchantPassword,
            $merchantCustomerId,
            $merchantInvoiceId,
            $merchantAccount,
            $merchantSiteId,
            $merchantProductId,
            $merchantDescriptor,
            $ipAddress,
            $referringMerchantId,
            $sharedSecret,
            $simplified3DS
        );
    }

    /**
     * @param null|string $merchantCustomerId Merchant customer Id
     * @return void
     * @throws Exception
     * @throws MissingMerchantInformationException
     */
    protected function validateMerchantCustomerId(?string $merchantCustomerId): void
    {
        if (empty($merchantCustomerId)) {
            throw new MissingMerchantInformationException('merchantCustomerId');
        }

        $this->merchantCustomerId = $merchantCustomerId;
    }
}
