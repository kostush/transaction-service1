<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\CardUploadCommand as RocketgateCardUploadCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

class MakeRocketgateCardUploadCommand extends ExternalCommand
{
    /**
     * @var RocketgateCardUploadAdapter
     */
    private $adapter;

    /**
     * @var RocketgateCardUploadCommand
     */
    private $rocketgateCardUploadCommand;

    /**
     * @var \DateTimeImmutable
     */
    private $requestDate;

    /**
     * MakeRocketgateCompleteThreeDCommand constructor.
     * @param RocketgateCardUploadAdapter $adapter                         Adapter
     * @param RocketgateCardUploadCommand $rocketgateCardUploadCommand Complete ThreeD Command
     * @param \DateTimeImmutable          $requestDate                     Request date
     */
    public function __construct(
        RocketgateCardUploadAdapter $adapter,
        RocketgateCardUploadCommand $rocketgateCardUploadCommand,
        \DateTimeImmutable $requestDate
    ) {
        $this->adapter                     = $adapter;
        $this->rocketgateCardUploadCommand = $rocketgateCardUploadCommand;
        $this->requestDate                 = $requestDate;
    }

    /**
     * Execute the command
     * @return RocketgateCreditCardBillerResponse
     * @throws \Exception
     */
    protected function run()
    {
        return $this->adapter->cardUpload($this->rocketgateCardUploadCommand, $this->requestDate);
    }

    /**
     * Fallback for failure
     * @return string
     * @throws \ProBillerNG\Logger\Exception
     * @throws \Exception
     */
    protected function getFallback()
    {
        Log::info('Rocketgate service error. Aborting transaction');

        // Return a abort transaction response
        return RocketgateCreditCardBillerResponse::createAbortedResponse($this->getExecutionException());
    }
}
