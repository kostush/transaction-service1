<?php

declare(strict_types=1);

namespace Tests\Unit\Integration\Rocketgate;

use ProBillerNG\Rocketgate\Application\Services\StartRebillCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\StopRebillCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommand;
use ProBillerNG\Rocketgate\Application\Services\UpdateRebillCommandHandler;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\UpdateRebillClient;
use Tests\UnitTestCase;

class UpdateRebillClientTest extends UnitTestCase
{
    /**
     * @var StartRebillCommandHandler
     */
    private $rocketgateStartRebillCommandHandler;

    /**
     * @var StopRebillCommandHandler
     */
    private $rocketgateStopRebillCommandHandler;

    /**
     * @var UpdateRebillCommandHandler
     */
    private $rocketgateUpdateRebillCommandHandler;

    /**
     * @var UpdateRebillCommand
     */
    private $updateRebillCommand;

    /**
     * @var UpdateRebillClient
     */
    private $client;

    /**
     * Setup before test
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rocketgateStartRebillCommandHandler = $this->createMock(
            StartRebillCommandHandler::class
        );
        $this->rocketgateStopRebillCommandHandler  = $this->createMock(
            StopRebillCommandHandler::class
        );

        $this->rocketgateUpdateRebillCommandHandler = $this->createMock(
            UpdateRebillCommandHandler::class
        );

        $this->updateRebillCommand = new UpdateRebillCommand(
            $this->faker->uuid,
            [
                'merchantId'         => $_ENV['ROCKETGATE_MERCHANT_ID_3'],
                'merchantPassword'   => $_ENV['ROCKETGATE_MERCHANT_PASSWORD_3'],
                'merchantCustomerId' => '3d4c0c07-6dc7-4116-b921-3353ae1d738d',
                'merchantInvoiceId'  => '8a534f01-5dc2cbf2390041.88295088'
            ],
            [
                "amount"    => 20,
                "frequency" => 365,
                "start"     => 30,
            ],
            [
                'amount'   => 100,
                'currency' => 'USD',
                'cardHash' => $_ENV['ROCKETGATE_CARD_HASH_1']
            ],
            true
        );

        $this->client = new UpdateRebillClient(
            $this->rocketgateStartRebillCommandHandler,
            $this->rocketgateStopRebillCommandHandler,
            $this->rocketgateUpdateRebillCommandHandler
        );
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function start_should_throw_a_rocketgate_service_exception_when_an_error_occurs_in_rocketgate_service()
    {
        $this->expectException(RocketgateServiceException::class);

        $this->rocketgateStartRebillCommandHandler->method('execute')->willThrowException(
            new \Exception()
        );

        $this->client->start($this->updateRebillCommand);
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function start_should_call_execute_method_on_rocketgate_handler(): void
    {
        $this->rocketgateStartRebillCommandHandler->expects($this->once())->method('execute');

        $this->client->start($this->updateRebillCommand);
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function stop_should_throw_a_rocketgate_service_exception_when_an_error_occurs_in_rocketgate_service()
    {
        $this->expectException(RocketgateServiceException::class);

        $this->rocketgateStopRebillCommandHandler->method('execute')->willThrowException(
            new \Exception()
        );

        $this->client->stop($this->updateRebillCommand);
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function stop_should_call_execute_method_on_rocketgate_handler(): void
    {
        $this->rocketgateStopRebillCommandHandler->expects($this->once())->method('execute');

        $this->client->stop($this->updateRebillCommand);
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function update_should_throw_a_rocketgate_service_exception_when_an_error_occurs_in_rocketgate_service()
    {
        $this->expectException(RocketgateServiceException::class);

        $this->rocketgateUpdateRebillCommandHandler->method('execute')->willThrowException(
            new \Exception()
        );

        $this->client->update($this->updateRebillCommand);
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function update_should_call_execute_method_on_rocketgate_handler(): void
    {
        $this->rocketgateUpdateRebillCommandHandler->expects($this->once())->method('execute');

        $this->client->update($this->updateRebillCommand);
    }
}
