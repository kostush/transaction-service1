<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use DateTimeImmutable;
use Exception;
use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommand as RocketgateSimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class MakeRocketgateSimplifiedCompleteThreeDCommand extends ExternalCommand
{
    /**
     * @var RocketgateSimplifiedCompleteThreeDAdapter
     */
    private $adapter;

    /**
     * @var RocketgateSimplifiedCompleteThreeDCommand
     */
    private $command;

    /**
     * @var DateTimeImmutable
     */
    private $requestDate;

    /**
     * MakeRocketgateCompleteThreeDCommand constructor.
     * @param RocketgateSimplifiedCompleteThreeDAdapter $adapter     Adapter
     * @param RocketgateSimplifiedCompleteThreeDCommand $command     Command
     * @param DateTimeImmutable                         $requestDate Request date
     */
    public function __construct(
        RocketgateSimplifiedCompleteThreeDAdapter $adapter,
        RocketgateSimplifiedCompleteThreeDCommand $command,
        DateTimeImmutable $requestDate
    ) {
        $this->adapter     = $adapter;
        $this->command     = $command;
        $this->requestDate = $requestDate;
    }

    /**
     * Execute the command
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     */
    protected function run()
    {
        return $this->adapter->simplifiedComplete($this->command, $this->requestDate);
    }

    /**
     * Fallback for failure
     * @return string
     * @throws \ProBillerNG\Logger\Exception
     * @throws Exception
     */
    protected function getFallback()
    {
        Log::info('Rocketgate service error. Aborting transaction');

        // Return a abort transaction response
        return RocketgateCreditCardBillerResponse::createAbortedResponse($this->getExecutionException());
    }
}
