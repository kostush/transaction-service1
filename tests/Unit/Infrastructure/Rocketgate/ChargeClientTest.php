<?php

declare(strict_types=1);

namespace Tests\Unit\Integration\Rocketgate;

use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommand;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithExistingCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommand;
use ProBillerNG\Rocketgate\Application\Services\ChargeWithNewCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommand;
use ProBillerNG\Rocketgate\Application\Services\CompleteThreeDCreditCardCommandHandler;
use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommand;
use ProBillerNG\Rocketgate\Application\Services\SuspendRebillCommandHandler;
use ProBillerNG\Transaction\Infrastructure\Domain\Services\Exception\RocketgateServiceException;
use ProBillerNG\Transaction\Infrastructure\Rocketgate\ChargeClient;
use Tests\UnitTestCase;

class ChargeClientTest extends UnitTestCase
{
    /**
     * @var ChargeWithNewCreditCardCommandHandler
     */
    private $rocketgateNewCreditCardChargeHandler;

    /**
     * @var ChargeWithExistingCreditCardCommandHandler
     */
    private $rocketgateExistingCreditCardChargeHandler;

    /**
     * @var ChargeWithExistingCreditCardCommandHandler
     */
    private $rocketgateSuspendRebillHandler;

    /**
     * @var CompleteThreeDCreditCardCommandHandler
     */
    private $rocketgateCompleteThreeDHandler;

    /**
     * @var ChargeClient
     */
    private $client;

    /**
     * Setup before test
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rocketgateExistingCreditCardChargeHandler = $this->createMock(
            ChargeWithExistingCreditCardCommandHandler::class
        );
        $this->rocketgateNewCreditCardChargeHandler      = $this->createMock(
            ChargeWithNewCreditCardCommandHandler::class
        );

        $this->rocketgateSuspendRebillHandler = $this->createMock(
            SuspendRebillCommandHandler::class
        );

        $this->rocketgateCompleteThreeDHandler = $this->createMock(
            CompleteThreeDCreditCardCommandHandler::class
        );

        $this->client = new ChargeClient(
            $this->rocketgateNewCreditCardChargeHandler,
            $this->rocketgateExistingCreditCardChargeHandler,
            $this->rocketgateSuspendRebillHandler,
            $this->rocketgateCompleteThreeDHandler
        );
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @return void
     */
    public function charge_new_credit_card_should_throw_a_rocketgate_service_exception_when_an_error_occurs_in_rocketgate_service(
    )
    {
        $this->expectException(RocketgateServiceException::class);

        $this->rocketgateNewCreditCardChargeHandler->method('execute')->willThrowException(
            new \Exception()
        );

        $this->client->chargeNewCreditCard(new ChargeWithNewCreditCardCommand('1', [], false, [], [], []));
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ReflectionException
     * @return void
     */
    public function charge_new_credit_card_should_accept_only_new_credit_card_command(): void
    {
        $this->expectException(\TypeError::class);

        $this->client->chargeNewCreditCard(
            $this->createMock(ChargeWithExistingCreditCardCommand::class)
        );
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ReflectionException
     * @return void
     */
    public function charge_exiting_credit_card_should_accept_only_existing_credit_card_command(): void
    {
        $this->expectException(\TypeError::class);

        $this->client->chargeExistingCreditCard(
            $this->createMock(ChargeWithNewCreditCardCommand::class)
        );
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ReflectionException
     * @return void
     */
    public function suspend_rebill_should_accept_only_suspend_rebill_command(): void
    {
        $this->expectException(\TypeError::class);

        $this->client->suspendRebill(
            $this->createMock(ChargeWithNewCreditCardCommand::class)
        );
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ReflectionException
     * @return void
     */
    public function suspend_rebill_should_call_execute_method_on_rocketgate_handler(): void
    {
        $this->rocketgateSuspendRebillHandler->expects($this->once())->method('execute');

        $this->client->suspendRebill(
            $this->createMock(SuspendRebillCommand::class)
        );
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ReflectionException
     * @return void
     */
    public function complete_threeD_should_accept_only_complete_threeD_command(): void
    {
        $this->expectException(\TypeError::class);

        $this->client->completeThreeD(
            $this->createMock(ChargeWithNewCreditCardCommand::class)
        );
    }

    /**
     * @test
     * @throws RocketgateServiceException
     * @throws \ProBillerNG\Logger\Exception
     * @throws \ReflectionException
     * @return void
     */
    public function complete_threeD_should_call_execute_method_on_rocketgate_handler(): void
    {
        $this->rocketgateCompleteThreeDHandler->expects($this->once())->method('execute');

        $this->client->completeThreeD(
            $this->createMock(CompleteThreeDCreditCardCommand::class)
        );
    }
}
