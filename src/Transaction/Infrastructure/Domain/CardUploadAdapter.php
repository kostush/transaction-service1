<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain;

use ProBillerNG\Rocketgate\Application\Services\CardUploadCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

interface CardUploadAdapter extends ChargeAdapterInterface
{
    /**
     * @param CardUploadCommand  $command     Complete ThreeD Command
     * @param \DateTimeImmutable $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     */
    public function cardUpload(
        CardUploadCommand $command,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse;
}
