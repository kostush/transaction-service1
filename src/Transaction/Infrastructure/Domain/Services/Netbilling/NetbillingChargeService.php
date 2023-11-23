<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\PaymentType;
use ProBillerNG\Transaction\Domain\Model\RebillUpdateTransaction;
use ProBillerNG\Transaction\Domain\Services\ChargeService;
use ProBillerNG\Transaction\Domain\Services\CreditCardCharge;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\NetbillingBillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\UnknownPaymentTypeForBillerException;

class NetbillingChargeService implements ChargeService
{

    /**
     * @var NetbillingCreditCardTranslationService
     */
    protected $creditCardTranslationService;

    /**
     * NetbillingChargeService constructor.
     * @param NetbillingCreditCardTranslationService $creditCardTranslationService Translation Service
     */
    public function __construct(NetbillingCreditCardTranslationService $creditCardTranslationService)
    {
        $this->creditCardTranslationService = $creditCardTranslationService;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse
     * @throws UnknownPaymentTypeForBillerException
     * @throws \Exception
     */
    public function chargeNewCreditCard(ChargeTransaction $transaction)
    {
        switch ($transaction->paymentType()) {
            case PaymentType::CREDIT_CARD:
                return $this->creditCardTranslationService->chargeWithNewCreditCard($transaction);
        }

        throw new UnknownPaymentTypeForBillerException($transaction->paymentType(), $transaction->billerName());
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse
     * @throws InvalidPaymentInformationException
     * @throws LoggerException
     * @throws UnknownPaymentTypeForBillerException
     */
    public function chargeExistingCreditCard(ChargeTransaction $transaction): BillerResponse
    {
        switch ($transaction->paymentType()) {
            case PaymentType::CREDIT_CARD:
                return $this->creditCardTranslationService->chargeWithExistingCreditCard($transaction);
        }

        throw new UnknownPaymentTypeForBillerException($transaction->paymentType(), $transaction->billerName());
    }

    /**
     * @param RebillUpdateTransaction $transaction Rebill Update Transaction
     * @return mixed
     * @throws \Exception
     */
    public function suspendRebill(RebillUpdateTransaction $transaction)
    {
        return $this->creditCardTranslationService->suspendRebill($transaction);
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string|null       $pares       Pares
     * @param string|null       $md          Netbilling biller transaction id
     * @param string|null       $cvv         Card CVV
     * @return BillerResponse
     */
    public function completeThreeDCreditCard(
        ChargeTransaction $transaction,
        ?string $pares,
        ?string $md,
        ?string $cvv = null
    ): BillerResponse {
        // TODO: Implement completeThreeDCreditCard() method.
    }

    /**
     * Implemented only for rocketgate
     * @param ChargeTransaction $transaction Transaction
     * @return BillerResponse|void
     */
    public function chargeOtherPaymentType(ChargeTransaction $transaction)
    {
        // TODO: Implement chargeOtherPaymentType() method.
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param string            $queryString Query string
     * @return BillerResponse
     */
    public function simplifiedCompleteThreeD(ChargeTransaction $transaction, string $queryString): BillerResponse
    {
        // TODO: Implement simplifiedCompleteThreeD() method.
    }
}
