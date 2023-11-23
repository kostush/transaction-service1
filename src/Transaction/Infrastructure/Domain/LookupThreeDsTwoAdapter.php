<?php

namespace ProBillerNG\Transaction\Infrastructure\Domain;

use ProBillerNG\Rocketgate\Application\Services\ThreeDSTwoLookupCommand;
use ProBillerNG\Transaction\Domain\Model\BillerResponse;

interface LookupThreeDsTwoAdapter
{
    /**
     * @param ThreeDSTwoLookupCommand $command     ThreeDSTwoLookupCommand
     * @param \DateTimeImmutable      $requestDate Request date
     * @return BillerResponse
     */
    public function performLookup(
        ThreeDSTwoLookupCommand $command,
        \DateTimeImmutable $requestDate
    ): BillerResponse;
}
