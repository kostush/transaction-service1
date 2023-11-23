<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

use JsonSerializable;
use ProBillerNG\Logger\Exception;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling\RetrieveNetbillingRebillUpdateTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrievePumapayRebillUpdateTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\RetrieveQyssoRebillTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveRebillUpdateTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class RebillUpdateTransactionQueryHttpDTO extends TransactionQueryHttpDTO implements JsonSerializable
{
    /**
     * @param Transaction $transaction Transaction
     * @return void
     * @throws Exception
     * @throws InvalidPaymentInformationException
     */
    protected function initTransaction(Transaction $transaction): void
    {
        switch ($transaction->billerName()) {
            case PumaPayBillerSettings::PUMAPAY:
                $this->transactionPayload = RetrievePumapayRebillUpdateTransactionReturnType::createFromEntity(
                    $transaction
                );
                break;
            case RocketGateBillerSettings::ROCKETGATE:
                $this->transactionPayload = RetrieveRebillUpdateTransactionReturnType::createFromEntity($transaction);
                break;
            case NetbillingBillerSettings::NETBILLING:
                $this->transactionPayload = RetrieveNetbillingRebillUpdateTransactionReturnType::createFromEntity(
                    $transaction
                );
                break;
            case QyssoBillerSettings::QYSSO:
                $this->transactionPayload = RetrieveQyssoRebillTransactionReturnType::createFromEntity($transaction);
                break;
        }
    }
}
