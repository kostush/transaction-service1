<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Epoch\Application\Services\NewSaleCommand;
use ProBillerNG\Logger\Log;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\EpochNewSaleBillerResponse;
use ProBillerNG\Transaction\Domain\Services\EpochNewSaleAdapter as EpochAdapterInterface;

class CircuitBreakerEpochNewSaleAdapter extends CircuitBreaker implements EpochAdapterInterface
{
    /**
     * @var EpochNewSaleAdapter
     */
    private $adapter;

    /**
     * Client constructor.
     * @param CommandFactory      $commandFactory      Command Factory
     * @param EpochNewSaleAdapter $epochNewSaleAdapter The Epoch New Sale adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        EpochNewSaleAdapter $epochNewSaleAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $epochNewSaleAdapter;
    }

    /**
     * @param NewSaleCommand $newSaleCommand CB Command
     * @return EpochNewSaleBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function newSale(NewSaleCommand $newSaleCommand): EpochNewSaleBillerResponse
    {
        $command = $this->commandFactory->getCommand(
            EpochNewSaleCommand::class,
            $this->adapter,
            $newSaleCommand
        );

        return $command->execute();
    }

}
