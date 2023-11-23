<?php
declare(strict_types=1);

namespace Infrastructure\Domain\Services\Netbilling;

use Exception;
use DateTimeImmutable;
use Tests\UnitTestCase;
use ProBillerNG\Netbilling\Application\Services\CancelRebillCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\MakeNetbillingSuspendCommand;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Netbilling\NetbillingCancelRebillAdapter;

class MakeNetbillingSuspendCommandTest extends UnitTestCase
{
    /**
     * @test
     * @return void
     * @throws Exception
     */
    public function it_should_return_an_aborted_netbilling_biller_response_in_case_of_exception_when_the_circuit_is_opened(): void
    {
        $netbillingCancelRebillAdapterMock = $this->createMock(NetbillingCancelRebillAdapter::class);
        $suspendRebillCommandMock          = $this->createMock(CancelRebillCommand::class);

        $netbillingCancelRebillAdapterMock->method('cancel')->willThrowException(new Exception());

        $command = $this->getCircuitBreakerCommandFactory()->getCommand(
            MakeNetbillingSuspendCommand::class,
            $netbillingCancelRebillAdapterMock,
            $suspendRebillCommandMock,
            new DateTimeImmutable()
        );

        $response = $command->execute();

        $this->assertTrue($response->aborted());
    }
}
