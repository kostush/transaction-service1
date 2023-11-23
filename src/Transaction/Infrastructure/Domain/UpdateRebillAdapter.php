<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Infrastructure\Domain;

use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Model\RocketgateCreditCardBillerResponse;

interface UpdateRebillAdapter
{
    /**
     * @param UpdateRebillCommand $command     Update Rebill Command
     * @param \DateTimeImmutable  $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     */
    public function start(
        UpdateRebillCommand $command,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse;

    /**
     * @param UpdateRebillCommand $command     Update Rebill Command
     * @param \DateTimeImmutable  $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     */
    public function stop(
        UpdateRebillCommand $command,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse;

    /**
     * @param UpdateRebillCommand $command     Update Rebill Command
     * @param \DateTimeImmutable  $requestDate The request date
     * @return RocketgateCreditCardBillerResponse
     */
    public function update(
        UpdateRebillCommand $command,
        \DateTimeImmutable $requestDate
    ): RocketgateCreditCardBillerResponse;
}
