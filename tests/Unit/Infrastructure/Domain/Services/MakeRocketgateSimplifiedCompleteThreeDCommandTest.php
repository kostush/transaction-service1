<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use DateTimeImmutable;
use Exception;
use ProBillerNG\Rocketgate\Application\Services\SimplifiedCompleteThreeDCommand as RocketgateSimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateSimplifiedCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSimplifiedCompleteThreeDAdapter;
use Tests\UnitTestCase;

class MakeRocketgateSimplifiedCompleteThreeDCommandTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_return_an_aborted_biller_response_when_the_circuit_is_opened(): void
    {
        $adapterMock = $this->createMock(RocketgateSimplifiedCompleteThreeDAdapter::class);
        $commandMock = $this->createMock(RocketgateSimplifiedCompleteThreeDCommand::class);

        $adapterMock->method('simplifiedComplete')->willThrowException(new Exception());

        $command = $this->getCircuitBreakerCommandFactory()->getCommand(
            MakeRocketgateSimplifiedCompleteThreeDCommand::class,
            $adapterMock,
            $commandMock,
            new DateTimeImmutable()
        );

        $response = $command->execute();

        self::assertTrue($response->aborted());
    }
}
