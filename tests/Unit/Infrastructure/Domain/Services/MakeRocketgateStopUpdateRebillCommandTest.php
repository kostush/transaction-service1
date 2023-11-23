<?php

namespace Tests\Unit\Infastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateStopUpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateUpdateRebillAdapter;
use Tests\UnitTestCase;

class MakeRocketgateStopUpdateRebillCommandTest extends UnitTestCase
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

        $adapterMock->method('stop')->willThrowException(new \Exception());

        $command = $this->getCircuitBreakerCommandFactory()->getCommand(
            MakeRocketgateStopUpdateRebillCommand::class,
            $adapterMock,
            $suspendRebillCommandMock,
            new \DateTimeImmutable()
        );

        $response = $command->execute();

        $this->assertTrue($response->aborted());
    }
}
