<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\IllegalStateTransitionException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerInteractionTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidThreedsVersionException;
use ProBillerNG\Transaction\Domain\Model\RocketGateChargeSettings;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\UnknownPaymentTypeForBillerException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateChargeService;

class ChargeThreeDService
{
    /**
     * @var RocketgateChargeService
     */
    protected $chargeService;

    /**
     * ChargeHandler constructor.
     * @param ChargeService $chargeService Charge service
     */
    public function __construct(ChargeService $chargeService)
    {
        $this->chargeService = $chargeService;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     *
     * @return BillerResponse
     * @throws Exception
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     */
    public function chargeNewCreditCard(ChargeTransaction $transaction): BillerResponse
    {
        $billerResponse = $this->chargeService->chargeNewCreditCard($transaction);

        if ($billerResponse->shouldRetryWithThreeD()) {
            Log::info('Retry transaction with 3DS');

            $transaction->updateTransactionWith3D(true);
            $transaction->updateTransactionBillerInteraction($billerResponse);

            $billerResponse = $this->chargeService->chargeNewCreditCard($transaction);
        }

        if ($billerResponse->shouldRetryWithoutThreeD()) {
            Log::info('Retry transaction without 3DS');

            $transaction->updateTransactionWith3D(false);
            $transaction->updateThreedsVersion(0);
            $transaction->updateTransactionBillerInteraction($billerResponse);

            $billerResponse = $this->chargeService->chargeNewCreditCard($transaction);
        }
        $transaction->updateRocketgateTransactionFromBillerResponse($billerResponse);

        return $billerResponse;
    }

    /**
     * @param ChargeTransaction $transaction Transaction.
     * @return BillerResponse
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws UnknownPaymentTypeForBillerException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function chargeExistingCreditCard(ChargeTransaction $transaction): BillerResponse
    {
        $billerResponse = $this->chargeService->chargeExistingCreditCard($transaction);

        /** @var RocketGateChargeSettings $billerChargeSettings */
        $billerChargeSettings = $transaction->billerChargeSettings();

        if ($billerResponse->shouldRetryWithThreeD((bool) $billerChargeSettings->simplified3DS())) {
            Log::info('Retry transaction with 3DS');

            $transaction->updateTransactionWith3D(true);
            $transaction->updateTransactionBillerInteraction($billerResponse);

            $billerResponse = $this->chargeService->chargeExistingCreditCard($transaction);
        }

        return $billerResponse;
    }

    /**
     * @param ChargeTransaction $transaction Charge transaction.
     * @return void
     *
     * @throws \ProBillerNG\Logger\Exception
     * @throws IllegalStateTransitionException
     * @throws InvalidBillerInteractionPayloadException
     * @throws InvalidBillerInteractionTypeException
     * @throws InvalidThreedsVersionException
     * @throws \JsonException
     */
    public function cardUpload(ChargeTransaction $transaction): void
    {
        Log::info("Retry card upload transaction for nsf");

        $newTransaction = clone $transaction;

        $billerResponse = $this->chargeService->cardUpload($newTransaction);
        $transaction->updateRocketgateTransactionFromBillerResponse($billerResponse);
    }
}
