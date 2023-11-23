<?php

namespace Tests\Unit\Infastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateStartUpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateUpdateRebillAdapter;
use Tests\UnitTestCase;

class MakeRocketgateStartUpdateRebillCommandTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_an_aborted_biller_response_when_the_circuit_is_opened(): void
    {
        $adapterMock              = $this->createMock(RocketgateUpdateRebillAdapter::class);
        $suspendRebillCommandMock = $this->createMock(UpdateRebillCommand::class);

        $adapterMock->method('start')->willThrowException(new \Exception());

        $command = $this->getCircuitBreakerCommandFactory()->getCommand(
            MakeRocketgateStartUpdateRebillCommand::class,
            $adapterMock,
            $suspendRebillCommandMock,
            new \DateTimeImmutable()
        );

        $response = $command->execute();

        $this->assertTrue($response->aborted());
    }
}
