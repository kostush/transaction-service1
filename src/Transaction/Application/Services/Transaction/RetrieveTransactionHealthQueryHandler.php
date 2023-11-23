<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

use ProBillerNG\Transaction\Application\DTO\TransactionHealthDTOAssembler;
use ProBillerNG\Transaction\Application\Services\Exception\InvalidQueryException;
use ProBillerNG\Transaction\Application\Services\Query;
use ProBillerNG\Transaction\Application\Services\QueryHandler;
use ProBillerNG\Transaction\Domain\Services\CircuitBreakerService;

class RetrieveTransactionHealthQueryHandler implements QueryHandler
{
    const HEALTH_OK   = 'OK';
    const HEALTH_DOWN = 'ERROR';

    /**
     * @var TransactionHealthDTOAssembler
     */
    private $assembler;

    /**
     * @var CircuitBreakerService
     */
    private $circuitBreakerService;

    /**
     * RetrieveTransactionHealthQueryHandler constructor.
     * @param CircuitBreakerService         $circuitBreakerService Circuit Breaker Service
     * @param TransactionHealthDTOAssembler $assembler             TransactionHealth DTO Assembler
     */
    public function __construct(CircuitBreakerService $circuitBreakerService, TransactionHealthDTOAssembler $assembler)
    {
        $this->assembler             = $assembler;
        $this->circuitBreakerService = $circuitBreakerService;
    }

    /**
     * @param Query $query Query
     * @return mixed
     * @throws InvalidQueryException
     * @throws \ProBillerNG\Logger\Exception
     */
    public function execute(Query $query)
    {
        if (!$query instanceof RetrieveTransactionHealthQuery) {
            throw new InvalidQueryException(RetrieveTransactionHealthQuery::class, $query);
        }

        $billers = [];

        foreach ($query->billerCommandMappings() as $billerName => $commands) {
            $billers[$billerName] = self::HEALTH_OK;
            foreach ($commands as $command) {
                if ($this->circuitBreakerService->isOpen($command)) {
                    $billers[$billerName] = self::HEALTH_DOWN;
                    break;
                }
            }
        }

        $status = self::HEALTH_OK;

        if (!in_array(self::HEALTH_OK, $billers)) {
            $status = self::HEALTH_DOWN;
        }

        $health = [
            'status'  => $status,
            'billers' => $billers,
        ];

        return $this->assembler->assemble($health);
    }
}
