<?php

namespace Tests\Unit\Infastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateUpdateRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateUpdateRebillAdapter;
use Tests\UnitTestCase;

class MakeRocketgateUpdateRebillCommandTest extends UnitTestCase
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

        $adapterMock->method('update')->willThrowException(new \Exception());

        $command = $this->getCircuitBreakerCommandFactory()->getCommand(
            MakeRocketgateUpdateRebillCommand::class,
            $adapterMock,
            $suspendRebillCommandMock,
            new \DateTimeImmutable()
        );

        $response = $command->execute();

        $this->assertTrue($response->aborted());
    }
}
