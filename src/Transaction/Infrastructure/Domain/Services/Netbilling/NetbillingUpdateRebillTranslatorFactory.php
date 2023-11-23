<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling;

use ProBillerNG\Transaction\Domain\Model\CreditCardInformation;
use ProBillerNG\Transaction\Domain\Model\PaymentInformation;

class NetbillingUpdateRebillTranslatorFactory
{
    /**
     * @param PaymentInformation $paymentInformation payment information
     * @return mixed
     */
    public function createUpdateRebillTranslator(PaymentInformation $paymentInformation): NetbillingUpdateRebillTranslator
    {
        if ($paymentInformation instanceof CreditCardInformation) {
            return app()->make(NetbillingUpdateRebillNewCardTranslator::class);
        }
        return app()->make(NetbillingUpdateRebillExistingCardTranslator::class);
    }
}
