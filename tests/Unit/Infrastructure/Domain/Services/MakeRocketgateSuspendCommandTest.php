<?php

namespace Tests\Unit\Infastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateSuspendCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateSuspendRebillAdapter;
use Tests\UnitTestCase;

class MakeRocketgateSuspendCommandTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_an_aborted_biller_response_when_the_circuit_is_opened(): void
    {
        $adapterMock              = $this->createMock(RocketgateSuspendRebillAdapter::class);
        $suspendRebillCommandMock = $this->createMock(SuspendRebillCommand::class);

        $adapterMock->method('suspend')->willThrowException(new \Exception());

        $command = $this->getCircuitBreakerCommandFactory()->getCommand(
            MakeRocketgateSuspendCommand::class,
            $adapterMock,
            $suspendRebillCommandMock,
            new \DateTimeImmutable()
        );

        $response = $command->execute();

        $this->assertTrue($response->aborted());
    }
}
