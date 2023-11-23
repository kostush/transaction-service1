<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain\Services;

use ProBillerNG\CircuitBreaker\ExternalCommand;
use ProBillerNG\Logger\Log;
use ProBillerNG\Rocketgate\Application\Services\ThreeDSTwoLookupCommand;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateLookupThreeDsTwoBillerResponse;

class MakeRocketgateLookupThreeDsTwoCommand extends ExternalCommand
{
    /**
     * @var RocketgateLookupThreeDsTwoAdapter
     */
    private $adapter;

    /**
     * @var ThreeDSTwoLookupCommand
     */
    private $threeDSTwoLookupCommand;

    /**
     * @var \DateTimeImmutable
     */
    private $requestDate;

    /**
     * MakeRocketgateLookupThreeDsTwoCommand constructor.
     * @param RocketgateLookupThreeDsTwoAdapter $adapter                 Adapter
     * @param ThreeDSTwoLookupCommand           $threeDSTwoLookupCommand ThreeDSTwoLookupCommand
     * @param \DateTimeImmutable                $requestDate             Request date
     */
    public function __construct(
        RocketgateLookupThreeDsTwoAdapter $adapter,
        ThreeDSTwoLookupCommand $threeDSTwoLookupCommand,
        \DateTimeImmutable $requestDate
    ) {
        $this->adapter                 = $adapter;
        $this->threeDSTwoLookupCommand = $threeDSTwoLookupCommand;
        $this->requestDate             = $requestDate;
    }

    /**
     * Execute the command
     * @return BillerResponse
     * @throws \Exception
     */
    protected function run(): BillerResponse
    {
        return $this->adapter->performLookup($this->threeDSTwoLookupCommand, $this->requestDate);
    }

    /**
     * Fallback for failure
     * @return RocketgateLookupThreeDsTwoBillerResponse
     * @throws \ProBillerNG\Logger\Exception
     */
    protected function getFallback(): RocketgateLookupThreeDsTwoBillerResponse
    {
        Log::info('Rocketgate service error. Aborting transaction');

        // Return a abort transaction response
        return RocketgateLookupThreeDsTwoBillerResponse::createAbortedResponse($this->getExecutionException());
    }
}
