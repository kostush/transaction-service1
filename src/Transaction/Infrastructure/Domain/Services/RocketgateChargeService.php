<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\PaymentMethod;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\UnknownPaymentMethodForBillerException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\UnknownPaymentTypeForBillerException;

class RocketgateChargeService implements ChargeService
{
    /**
     * @var RocketgateCreditCardTranslationService
     */
    protected $rocketgateCreditCardTranslationService;

    /**
     * @var RocketgateOtherPaymentTypeChargeAdapter
     */
    protected $rocketgateOtherPaymentTypeAdapter;

    /**
     * ChargeHandler constructor.
     * @param RocketgateCreditCardTranslationService  $rocketgateCreditCardTranslationService Charge credit card handler
     * @param RocketgateOtherPaymentTypeChargeAdapter $rocketgateOtherPaymentTypeAdapter      Other Payment Adapter
     */
    public function __construct(
        RocketgateCreditCardTranslationService $rocketgateCreditCardTranslationService,
        RocketgateOtherPaymentTypeChargeAdapter $rocketgateOtherPaymentTypeAdapter
    ) {
        $this->rocketgateCreditCardTranslationService = $rocketgateCreditCardTranslationService;
        $this->rocketgateOtherPaymentTypeAdapter      = $rocketgateOtherPaymentTypeAdapter;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     *
     * @return RocketgateBillerResponse
     * @throws UnknownPaymentTypeForBillerException
     * @throws Exception
     */
    public function chargeNewCreditCard(ChargeTransaction $transaction): RocketgateBillerResponse
    {
        switch ($transaction->paymentType()) {
            case PaymentType::CREDIT_CARD:
                return $this->rocketgateCreditCardTranslationService->chargeWithNewCreditCard($transaction);
        }

        throw new UnknownPaymentTypeForBillerException($transaction->paymentType(), $transaction->billerName());
    }

    /**
     * @param ChargeTransaction $transaction The Transaction object
     * @return RocketgateBillerResponse
     * @throws UnknownPaymentTypeForBillerException
     * @throws Exception
     */
    public function chargeExistingCreditCard(ChargeTransaction $transaction): RocketgateBillerResponse
    {
        switch ($transaction->paymentType()) {
            case PaymentType::CREDIT_CARD:
                return $this->rocketgateCreditCardTranslationService->chargeWithExistingCreditCard($transaction);
        }

        throw new UnknownPaymentTypeForBillerException($transaction->paymentType(), $transaction->billerName());
    }

    /**
     * Note: Maybe we should have only one method 'charge' with a switch inside.
     *
     * @param ChargeTransaction $transaction Transaction
     *
     * @return BillerResponse|void
     * @throws UnknownPaymentMethodForBillerException
     * @throws \Exception
     */
    public function chargeOtherPaymentType(ChargeTransaction $transaction)
    {
        switch ($transaction->paymentMethod()) {
            case PaymentMethod::CHECKS:
                return $this->rocketgateOtherPaymentTypeAdapter->charge($transaction);
        }

        throw new UnknownPaymentMethodForBillerException($transaction->paymentMethod(), $transaction->billerName());
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     *
     * @return mixed|RocketgateCreditCardBillerResponse
     * @throws Exception
     */
    public function suspendRebill(RebillUpdateTransaction $transaction)
    {
        return $this->rocketgateCreditCardTranslationService->suspendRebill($transaction);
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string|null       $pares       Pares
     * @param string|null       $md          Rocketgate biller transaction id
     * @param string|null       $cvv         CVV retrieved from Redis
     * @return BillerResponse|RocketgateCreditCardBillerResponse
     * @throws Exception
     */
    public function completeThreeDCreditCard(
        ChargeTransaction $transaction,
        ?string $pares,
        ?string $md,
        ?string $cvv = null
    ): BillerResponse {
        return $this->rocketgateCreditCardTranslationService->completeThreeDCreditCard($transaction, $pares, $md, $cvv);
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string            $queryString Query string
     * @return BillerResponse
     * @throws Exception
     */
    public function simplifiedCompleteThreeD(
        ChargeTransaction $transaction,
        string $queryString
    ): BillerResponse {
        return $this->rocketgateCreditCardTranslationService->simplifiedCompleteThreeD(
            $transaction,
            $queryString
        );
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     *
     * @return BillerResponse|RocketgateCreditCardBillerResponse
     * @throws Exception
     */
    public function cardUpload(ChargeTransaction $transaction)
    {
        return $this->rocketgateCreditCardTranslationService->cardUpload($transaction);
    }
}
