<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Domain\Services;

use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\MakeRocketgateCompleteThreeDCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\RocketgateCompleteThreeDCreditCardAdapter;
use Tests\UnitTestCase;

class MakeRocketgateCompleteThreeDCommandTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function it_should_return_an_aborted_biller_response_when_the_circuit_is_opened(): void
    {
        $adapterMock              = $this->createMock(RocketgateCompleteThreeDCreditCardAdapter::class);
        $suspendRebillCommandMock = $this->createMock(CompleteThreeDCreditCardCommand::class);

        $adapterMock->method('complete')->willThrowException(new \Exception());

        $command = $this->getCircuitBreakerCommandFactory()->getCommand(
            MakeRocketgateCompleteThreeDCommand::class,
            $adapterMock,
            $suspendRebillCommandMock,
            new \DateTimeImmutable()
        );

        $response = $command->execute();

        $this->assertTrue($response->aborted());
    }
}