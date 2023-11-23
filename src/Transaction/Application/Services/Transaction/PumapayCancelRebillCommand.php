<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;

class PumapayCancelRebillCommand extends Command
{
    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var string
     */
    private $businessId;

    /**
     * @var string
     */
    private $businessModel;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * PumapayCancelRebillCommand constructor.
     * @param string $transactionId
     * @param string $businessId
     * @param string $businessModel
     * @param string $apiKey
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function __construct(
        string $transactionId,
        string $businessId,
        string $businessModel,
        string $apiKey
    ) {
        $this->initTransactionId($transactionId);
        $this->initBusinessId($businessId);
        $this->initBusinessModel($businessModel);
        $this->initApiKey($apiKey);
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
    public function businessId(): string
    {
        return $this->businessId;
    }

    /**
     * @return string
     */
    public function businessModel(): string
    {
        return $this->businessModel;
    }

    /**
     * @return string
     */
    public function apiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $transactionId Transaction Id
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initTransactionId(string $transactionId): void
    {
        if (empty($transactionId)) {
            throw new MissingChargeInformationException('transactionId');
        }

        $this->transactionId = $transactionId;
    }

    /**
     * @param string $businessId Business Id
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initBusinessId(string $businessId): void
    {
        if (empty($businessId)) {
            throw new MissingChargeInformationException('businessId');
        }

        $this->businessId = $businessId;
    }

    /**
     * @param string $businessModel Business model
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initBusinessModel(string $businessModel): void
    {
        if (empty($businessModel)) {
            throw new MissingChargeInformationException('businessModel');
        }

        $this->businessModel = $businessModel;
    }

    /**
     * @param string $apiKey Api key
     * @throws MissingChargeInformationException
     * @throws \ProBillerNG\Logger\Exception
     */
    private function initApiKey(string $apiKey): void
    {
        if (empty($apiKey)) {
            throw new MissingChargeInformationException('apiKey');
        }

        $this->apiKey = $apiKey;
    }
}
