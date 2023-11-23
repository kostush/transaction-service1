<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Transaction\Application\Services\Transaction\Member;
use ProBillerNG\Transaction\Domain\Model\ChargesCollection;
use ProBillerNG\Transaction\Domain\Model\ChargeTransaction;
use ProBillerNG\Transaction\Domain\Services\LegacyNewSaleAdapter;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\LegacyNewSaleBillerResponse;

class CircuitBreakerLegacyNewSaleAdapter extends CircuitBreaker implements LegacyNewSaleAdapter
{
    /**
     * @var LegacyNewSaleAdapter
     */
    private $adapter;

    /**
     * Client constructor.
     * @param CommandFactory       $commandFactory       Command Factory
     * @param LegacyNewSaleAdapter $legacyNewSaleAdapter The Legacy New Sale adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        LegacyNewSaleAdapter $legacyNewSaleAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $legacyNewSaleAdapter;
    }

    /**
     * @param ChargeTransaction $transaction Transaction
     * @param ChargesCollection $charges     Charges
     * @param Member            $member      Member
     * @return LegacyNewSaleBillerResponse
     */
    public function newSale(
        ChargeTransaction $transaction,
        ChargesCollection $charges,
        ?Member $member
    ): LegacyNewSaleBillerResponse {
        $command = $this->commandFactory->getCommand(
            LegacyNewSaleCommand::class,
            $this->adapter,
            $transaction,
            $charges,
            $member
        );

        return $command->execute();
    }
}
