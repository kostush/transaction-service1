<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use DateTimeImmutable;
use Exception;
use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommand as RocketgateCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class MakeRocketgateCompleteThreeDCommand extends ExternalCommand
{
    /**
     * @var RocketgateCompleteThreeDCreditCardAdapter
     */
    private $adapter;

    /**
     * @var RocketgateCompleteThreeDCommand
     */
    private $rocketgateCompleteThreeDCommand;

    /**
     * @var DateTimeImmutable
     */
    private $requestDate;

    /**
     * MakeRocketgateCompleteThreeDCommand constructor.
     * @param RocketgateCompleteThreeDCreditCardAdapter $adapter                         Adapter
     * @param RocketgateCompleteThreeDCommand           $rocketgateCompleteThreeDCommand Complete ThreeD Command
     * @param DateTimeImmutable                         $requestDate                     Request date
     */
    public function __construct(
        RocketgateCompleteThreeDCreditCardAdapter $adapter,
        RocketgateCompleteThreeDCommand $rocketgateCompleteThreeDCommand,
        DateTimeImmutable $requestDate
    ) {
        $this->adapter                         = $adapter;
        $this->rocketgateCompleteThreeDCommand = $rocketgateCompleteThreeDCommand;
        $this->requestDate                     = $requestDate;
    }

    /**
     * Execute the command
     * @return RocketgateCreditCardBillerResponse
     * @throws Exception
     */
    protected function run()
    {
        return $this->adapter->complete($this->rocketgateCompleteThreeDCommand, $this->requestDate);
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
