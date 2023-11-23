<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Epoch\RetrieveEpochChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Legacy\RetrieveLegacyChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Netbilling\RetrieveNetbillingChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Pumapay\RetrievePumapayChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\Qysso\RetrieveQyssoChargeTransactionReturnType;
use ProBillerNG\Transaction\Application\DTO\ReturnTypes\RetrieveChargeTransactionReturnType;
use ProBillerNG\Transaction\Domain\Model\Exception\UnknownBillerNameException;
use ProBillerNG\Transaction\Domain\Model\EpochBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPaymentInformationException;
use ProBillerNG\Transaction\Domain\Model\LegacyBillerChargeSettings;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;

class ChargeTransactionQueryHttpDTO extends TransactionQueryHttpDTO implements \JsonSerializable
{
    /**
     * @param Transaction $transaction Transaction
     * @return void
     * @throws InvalidPaymentInformationException
     * @throws LoggerException|UnknownBillerNameException
     */
    protected function initTransaction(Transaction $transaction): void
    {
        //TODO need to create version2 of transaction retrieval api because
        // we need to separate the biller specific fields for each biller
        // jira ticket BG-38293

        switch ($transaction->billerName()) {
            case PumaPayBillerSettings::PUMAPAY:
                $this->transactionPayload = RetrievePumapayChargeTransactionReturnType::createFromEntity($transaction);
                break;
            case RocketGateBillerSettings::ROCKETGATE:
                $this->transactionPayload = RetrieveChargeTransactionReturnType::createFromEntity($transaction);
                break;
            case NetbillingBillerSettings::NETBILLING:
                $this->transactionPayload = RetrieveNetbillingChargeTransactionReturnType::createFromEntity(
                    $transaction
                );
                break;
            case EpochBillerSettings::EPOCH:
                $this->transactionPayload = RetrieveEpochChargeTransactionReturnType::createFromEntity($transaction);
                break;
            case LegacyBillerChargeSettings::LEGACY:
                $this->transactionPayload = RetrieveLegacyChargeTransactionReturnType::createFromEntity($transaction);
                break;
            case QyssoBillerSettings::QYSSO:
                $this->transactionPayload = RetrieveQyssoChargeTransactionReturnType::createFromEntity($transaction);
                break;
            default:
                throw new UnknownBillerNameException($transaction->billerName());
        }
    }
}
