<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Services;

use ProBillerNG\Logger\Exception;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Application\Services\Transaction\RetrievePumapayQrCodeCommand;
use ProBillerNG\Transaction\Domain\Model\BillerSettings;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\PreviousTransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionAlreadyProcessedException;
use ProBillerNG\Transaction\Domain\Model\PumaPayBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;

class PumapayQrCodeTransactionService implements PumapayTransactionService
{
    /**
     * @var TransactionRepository
     */
    private $repository;

    /**
     * PumapayQrCodeTransactionService constructor.
     * @param TransactionRepository $repository Repository.
     */
    public function __construct(TransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param RetrievePumapayQrCodeCommand $command Command
     * @return ChargeTransaction
     * @throws Exception
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     * @throws PreviousTransactionNotFoundException
     * @throws InvalidBillerNameException
     * @throws TransactionAlreadyProcessedException
     */
    public function createOrUpdateTransaction(RetrievePumapayQrCodeCommand $command): ChargeTransaction
    {
        if (is_null($command->transactionId())) {
            Log::info('Creating new pumapay transaction');

            return ChargeTransaction::createSingleChargeOnPumapay(
                $command->siteId(),
                $command->amount(),
                PumaPayBillerSettings::PUMAPAY,
                $command->currency(),
                $command->businessId(),
                $command->businessModel(),
                $command->apiKey(),
                $command->title(),
                $command->description(),
                $command->rebill()
            );
        }
        /** @var ChargeTransaction $transaction */
        $transaction = $this->repository->findById($command->transactionId());
        $this->validateTransaction($transaction, $command->transactionId());

        Log::info('Updating pumapay transaction');
        $transaction->updatePumapayToReceiveQrCode(
            $command->siteId(),
            $command->amount(),
            $command->currency(),
            $command->businessId(),
            $command->businessModel(),
            $command->apiKey(),
            $command->title(),
            $command->description(),
            $command->rebill()
        );

        return $transaction;
    }

    /**
     * @param Transaction $transaction   Transaction.
     * @param string      $transactionId TransactionId.
     * @return void
     * @throws PreviousTransactionNotFoundException*
     * @throws Exception
     * @throws InvalidBillerNameException
     * @throws TransactionAlreadyProcessedException
     */
    private function validateTransaction(?Transaction $transaction, string $transactionId): void
    {
        if (!$transaction instanceof ChargeTransaction) {
            throw new PreviousTransactionNotFoundException($transactionId);
        }

        if ($this->isNotPumapay($transaction)) {
            throw new InvalidBillerNameException(
                $transaction->billerChargeSettings()->billerName(),
                PumaPayBillerSettings::PUMAPAY
            );
        }

        if (!$transaction->status()->pending()) {
            throw new TransactionAlreadyProcessedException($transactionId);
        }
    }

    /**
     * Why Crypto:
     * - When cascade sends us 'crypto', we'll create a pending transaction with 'crypto' as biller name.
     * - In this case, we'll only know the exact biller (e.g. Pumapay, Coinpayment)
     *   after the user decision on the gateway page.
     * @param Transaction|null $transaction Transaction retrieved
     * @return bool
     */
    private function isNotPumapay(?Transaction $transaction): bool
    {
        return (strcasecmp($transaction->billerChargeSettings()->billerName(), PumaPayBillerSettings::PUMAPAY) !== 0)
               && (strcasecmp($transaction->billerChargeSettings()->billerName(), BillerSettings::CRYPTO) !== 0);
    }
}
