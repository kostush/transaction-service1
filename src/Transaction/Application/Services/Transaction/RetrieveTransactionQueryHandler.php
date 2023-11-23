<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Logger\Exception as LoggerException;
use ProBillerNG\Transaction\Application\DTO\HttpQueryDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidQueryException;
use ProBillerNG\Transaction\Application\Services\Query;
use ProBillerNG\Transaction\Application\Services\QueryHandler;
use ProBillerNG\Transaction\Domain\Model\Exception\InvalidPayloadException;
use ProBillerNG\Transaction\Domain\Model\Exception\RetrieveTransactionException;
use ProBillerNG\Transaction\Domain\Model\Exception\TransactionNotFoundException;
use ProBillerNG\Transaction\Domain\Model\Exception\UnknownBillerNameException;
use ProBillerNG\Transaction\Domain\Model\Transaction;
use ProBillerNG\Transaction\Domain\Model\TransactionId;
use ProBillerNG\Transaction\Domain\Model\TransactionRepository;
use ProBillerNG\Transaction\Domain\Services\Exception\ChargeSettingsNotFoundException;

class RetrieveTransactionQueryHandler implements QueryHandler
{
    /** @var TransactionRepository */
    private $transactionRepository;

    /** @var HttpQueryDTOAssembler */
    private $assembler;

    /**
     * RetrieveTransactionQueryHandler constructor.
     * @param TransactionRepository $transactionRepository Transaction repository
     * @param HttpQueryDTOAssembler $assembler             Transaction DTO Assembler
     */
    public function __construct(TransactionRepository $transactionRepository, HttpQueryDTOAssembler $assembler)
    {
        $this->transactionRepository = $transactionRepository;
        $this->assembler             = $assembler;
    }

    /**
     * @param Query $query Query
     * @return mixed
     * @throws RetrieveTransactionException
     * @throws TransactionNotFoundException
     * @throws InvalidPayloadException
     * @throws LoggerException
     */
    public function execute(Query $query)
    {
        try {
            if (!$query instanceof RetrieveTransactionQuery) {
                throw new InvalidQueryException(RetrieveTransactionQuery::class, $query);
            }

            $transaction = $this->transactionRepository->findById(
                (string) TransactionId::createFromString($query->transactionId())
            );

            if (!$transaction instanceof Transaction) {
                throw new TransactionNotFoundException($query->transactionId());
            }

            return $this->assembler->assemble($transaction);
        } catch (TransactionNotFoundException | InvalidPayloadException | UnknownBillerNameException $e) {
            throw $e;
        } catch (ChargeSettingsNotFoundException | \Exception $e) {
            throw new RetrieveTransactionException($e);
        }
    }
}
