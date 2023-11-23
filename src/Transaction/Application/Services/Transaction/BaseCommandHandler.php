<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\DTO\TransactionDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Command;
use ProBillerNG\Transaction\Application\Services\CommandHandler;
use ProBillerNG\Transaction\Application\Services\Transaction\Netbilling\PerformNetbillingNewCreditCardSaleCommand;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Model\EpochBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardCvvException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardExpirationDateException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardNumberException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidCreditCardTypeException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingChargeInformationException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingInitialDaysException;
use ProBillerNG\Transaction\Domain\Model\Exception\MissingMerchantInformationException;
use ProBillerNG\Transaction\Domain\Model\Netbilling\NetbillingBillerSettings;
use ProBillerNG\Transaction\Domain\Model\QyssoBillerSettings;
use ProBillerNG\Transaction\Domain\Model\RocketGateBillerSettings;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;

abstract class BaseCommandHandler implements CommandHandler
{
    /**
     * @var TransactionRepository
     */
    protected $repository;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var TransactionDTOAssembler
     */
    protected $dtoAssembler;

    /**
     * CreateTransactionHandler constructor.
     *
     * @param TransactionRepository   $repository   Transaction repository
     * @param TransactionDTOAssembler $dtoAssembler DTO Assembler
     */
    public function __construct(TransactionRepository $repository, TransactionDTOAssembler $dtoAssembler)
    {
        $this->repository   = $repository;
        $this->dtoAssembler = $dtoAssembler;
    }

    /**
     * @param Command $command Command
     * @return ChargeTransaction
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws LoggerException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     */
    protected function createRocketgateTransaction(Command $command): ChargeTransaction
    {
        if (empty($command->rebill())) {
            return ChargeTransaction::createSingleChargeOnRocketgate(
                $command->siteId(),
                $command->amount(),
                RocketGateBillerSettings::ROCKETGATE,
                $command->currency(),
                $command->payment(),
                $command->billerFields(),
                $command->useThreeD(),
                $command->returnUrl()
            );
        }

        return ChargeTransaction::createWithRebillOnRocketgate(
            $command->siteId(),
            $command->amount(),
            RocketGateBillerSettings::ROCKETGATE,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->rebill(),
            $command->useThreeD(),
            $command->returnUrl()
        );
    }

    /**
     * @param Command $command The command
     * @return ChargeTransaction
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createQyssoTransaction(Command $command): ChargeTransaction
    {
        /** @var PerformQyssoNewSaleCommand $command */
        if (empty($command->rebill())) {
            return ChargeTransaction::createSingleChargeOnEpoch(
                $command->siteId(),
                $command->siteName(),
                QyssoBillerSettings::QYSSO,
                $command->amount(),
                $command->currency(),
                $command->paymentType(),
                $command->paymentMethod(),
                $command->billerFields(),
                $command->member() ? $command->member()->userName() : null,
                $command->member() ? $command->member()->password() : null
            );
        }

        return ChargeTransaction::createWithRebillOnEpoch(
            $command->siteId(),
            $command->siteName(),
            QyssoBillerSettings::QYSSO,
            $command->amount(),
            $command->currency(),
            $command->paymentType(),
            $command->paymentMethod(),
            $command->billerFields(),
            $command->rebill(),
            $command->member() ? $command->member()->userName() : null,
            $command->member() ? $command->member()->password() : null
        );
    }

    /**
     * @param Command $command The command
     * @return ChargeTransaction
     * @throws LoggerException
     * @throws InvalidChargeInformationException
     * @throws MissingChargeInformationException
     */
    protected function createEpochTransaction(Command $command): ChargeTransaction
    {
        /** @var PerformEpochNewSaleCommand $command */
        if (empty($command->rebill())) {
            return ChargeTransaction::createSingleChargeOnEpoch(
                $command->siteId(),
                $command->siteName(),
                EpochBillerSettings::EPOCH,
                $command->amount(),
                $command->currency(),
                $command->paymentType(),
                $command->paymentMethod(),
                $command->billerFields(),
                $command->member() ? $command->member()->userName() : null,
                $command->member() ? $command->member()->password() : null
            );
        }

        return ChargeTransaction::createWithRebillOnEpoch(
            $command->siteId(),
            $command->siteName(),
            EpochBillerSettings::EPOCH,
            $command->amount(),
            $command->currency(),
            $command->paymentType(),
            $command->paymentMethod(),
            $command->billerFields(),
            $command->rebill(),
            $command->member() ? $command->member()->userName() : null,
            $command->member() ? $command->member()->password() : null
        );
    }

    /**
     * @param Command $command command
     * @return ChargeTransaction
     * @throws InvalidChargeInformationException
     * @throws InvalidCreditCardCvvException
     * @throws InvalidCreditCardExpirationDateException
     * @throws InvalidCreditCardNumberException
     * @throws InvalidCreditCardTypeException
     * @throws InvalidMerchantInformationException
     * @throws LoggerException
     * @throws MissingChargeInformationException
     * @throws MissingMerchantInformationException
     * @throws InvalidCreditCardInformationException
     * @throws InvalidPayloadException
     * @throws MissingInitialDaysException
     */
    protected function createNetbillingTransaction(Command $command): ChargeTransaction
    {
        /** @var PerformNetbillingNewCreditCardSaleCommand $command */
        if (empty($command->rebill())) {
            return ChargeTransaction::createSingleChargeOnNetbilling(
                $command->siteId(),
                $command->amount(),
                NetbillingBillerSettings::NETBILLING,
                $command->currency(),
                $command->payment(),
                $command->billerFields(),
                $command->billerLoginInfo()
            );
        }

        return ChargeTransaction::createWithRebillOnNetbilling(
            $command->siteId(),
            $command->amount(),
            NetbillingBillerSettings::NETBILLING,
            $command->currency(),
            $command->payment(),
            $command->billerFields(),
            $command->rebill(),
            $command->billerLoginInfo()
        );
    }
}
