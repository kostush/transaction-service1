<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use Odesk\Phystrix\CommandFactory;
use ProBillerNG\CircuitBreaker\CircuitBreaker;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\ThreeDSTwoLookupCommand;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\LookupThreeDsTwoAdapter;

class CircuitBreakerRocketgateLookupThreeDsTwoAdapter extends CircuitBreaker implements LookupThreeDsTwoAdapter
{
    /**
     * @var RocketgateLookupThreeDsTwoAdapter
     */
    private $adapter;

    /**
     * CircuitBreakerRocketgateLookupThreeDsTwoAdapter constructor.
     * @param CommandFactory                    $commandFactory Command Factory
     * @param RocketgateLookupThreeDsTwoAdapter $lookupAdapter  Lookup adapter
     */
    public function __construct(
        CommandFactory $commandFactory,
        RocketgateLookupThreeDsTwoAdapter $lookupAdapter
    ) {
        parent::__construct($commandFactory);

        $this->adapter = $lookupAdapter;
    }

    /**
     * @param ThreeDSTwoLookupCommand $lookupCommand Lookup command
     * @param \DateTimeImmutable      $requestDate   Request date
     * @return BillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    public function performLookup(
        ThreeDSTwoLookupCommand $lookupCommand,
        \DateTimeImmutable $requestDate
    ): BillerResponse {
        Log::info('Send Rocketgate lookup threeDs 2 request');

        $command = $this->commandFactory->getCommand(
            MakeRocketgateLookupThreeDsTwoCommand::class,
            $this->adapter,
            $lookupCommand,
            $requestDate
        );

        return $command->execute();
    }
}
